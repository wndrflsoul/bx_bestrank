<?php

namespace Bitrix\TransformerController\Daemon;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Dto\Job;
use Bitrix\TransformerController\Daemon\Dto\Statistic;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;
use Bitrix\TransformerController\Daemon\File\Type;
use Bitrix\TransformerController\Daemon\File\Type\Document;
use Bitrix\TransformerController\Daemon\File\Type\Video;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Response;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Transformation\Command;
use Bitrix\TransformerController\Daemon\Transformation\ConverterRegistry;
use Psr\Log\LoggerInterface;

final class JobProcessor
{
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly BanList $banList,
		private readonly LoggerFactory $loggerFactory,
		private readonly ConverterRegistry $converterRegistry,
	)
	{
	}

	public function process(Job $job): void
	{
		$stats = new Statistic();
		$stats->startTimestamp = time();

		$this->logger->info(
			'get {commandName} with id {guid}',
			[
				'commandName' => $job->commandClass,
				'guid' => $job->guid,
			],
		);

		$jobResult = $this->processJob($job, $stats);

		$criticalError = $this->getFirstCriticalError($jobResult);
		if ($criticalError)
		{
			$stats->error = $criticalError;
		}
		else
		{
			$stats->error = current($jobResult->getErrors()) ?: null;
		}

		// we dont notify a banned client
		if ($criticalError?->getCode() !== Error\Dictionary::DOMAIN_IS_BANNED)
		{
			$notifyResult =
				(new Request\NotifyClientAboutJobFinish($job->backUrl, $jobResult->getData(), $criticalError))
					->setLoggerFluently($this->createHttpLogger($job))
					->send()
			;

			if ($notifyResult->isSuccess())
			{
				$this->logger->info(
					'Completed command',
					[
						'guid' => $job->guid,
						'responseOnComplete' => Utils::cutResponse($notifyResult->getDataKey('content')),
					],
				);
			}
		}

		DeleteQueue::getInstance()->flush();

		$stats->endTimestamp = time();

		(new Request\Controller\AddStatistic($job->guid, $stats))
			->setLoggerFluently($this->createHttpLogger($job))
			->send()
		;
	}

	private function processJob(Job $job, Statistic $stats): Result
	{
		$checkBanResult = $this->banList->checkIfBanned($job);
		if (!$checkBanResult->isSuccess())
		{
			return $checkBanResult;
		}

		$fileType = $this->getFileType($job);
		if (!$fileType)
		{
			$this->logger->error(
				'Unknown command {commandName}',
				['commandName' => $job->commandClass, 'guid' => $job->guid],
			);

			return (new Result())->addError(
				new Error(
					"command {$job->commandClass} not found",
					Error\Dictionary::COMMAND_NOT_FOUND,
				)
			);
		}

		$filePath = null;
		$fileSize = null;
		if ($job->fileUrl)
		{
			$downloadStart = microtime(true);
			$downloadResult = $this->downloadFile($job, $fileType);
			$downloadFinish = microtime(true);

			$stats->timeDownload = round($downloadFinish - $downloadStart);

			if (!$downloadResult->isSuccess())
			{
				return $downloadResult;
			}

			$filePath = $downloadResult->getDataKey('file');
			$fileSize = $downloadResult->getDataKey('fileSize');

			$stats->fileSize = $fileSize;
		}

		$execStart = microtime(true);
		$command = new Command(
			$this->converterRegistry,
			$fileType,
			$job->formats,
			$filePath,
			$fileSize,
			$this->createCommandLogger($job),
		);
		$jobResult = $command->execute();
		$execFinish = microtime(true);
		$stats->timeExec = round($execFinish - $execStart);

		if ($jobResult->getDataKey('files'))
		{
			$uploadStart = microtime(true);
			$uploadResult = $this->uploadFiles($job, $jobResult->getDataKey('files'));
			$uploadFinish = microtime(true);
			$stats->timeUpload = round($uploadFinish - $uploadStart);

			$uploadedFiles = $uploadResult->getDataKey('files');
			$jobResult->setDataKey('files', $uploadedFiles);

			if (!$uploadResult->isSuccess())
			{
				$jobResult->addErrors($uploadResult->getErrors());
			}
		}

		return $jobResult;
	}

	private function getFileType(Job $job): ?File\Type
	{
		$trimmed = ltrim($job->commandClass, '\\');

		if (
			$trimmed === 'Bitrix\TransformerController\Document'
			|| $trimmed === 'Bitrix\\TransformerController\\Document'
		)
		{
			return new Document();
		}

		if (
			$trimmed === 'Bitrix\TransformerController\Video'
			|| $trimmed === 'Bitrix\\TransformerController\\Video'
		)
		{
			return new Video($job->tarif);
		}

		return null;
	}

	private function downloadFile(Job $job, Type $fileType): Result
	{
		return (new Request\File\Download($job->fileUrl, $fileType))
			->setLoggerFluently($this->createHttpLogger($job))
			->send()
		;
	}

	/**
	 * @param Job $job
	 * @param Array<string, string> $formatToFilePathMap
	 *
	 * @return Result
	 */
	private function uploadFiles(Job $job, array $formatToFilePathMap): Result
	{
		$result = new Result();
		$files = [];

		foreach ($formatToFilePathMap as $format => $filePath)
		{
			$this->logger->debug(
				'Preparing to upload file {filePath} (format {format})',
				[
					'filePath' => $filePath,
					'format' => $format,
					'backUrl' => $job->backUrl,
				]
			);

			$fileSize = filesize($filePath);

			$getInfoResult =
				(new Request\File\Upload\GetInfo($job->backUrl, $format, $fileSize))
					->setLoggerFluently($this->createHttpLogger($job))
					->send()
			;

			if (!$getInfoResult->isSuccess())
			{
				$result->addErrors($getInfoResult->getErrors());

				continue;
			}

			//todo replace 'response' classes with dto (it should be 1-2 classes at most)
			/** @var \Bitrix\TransformerController\Daemon\Http\Response\File\Upload\GetInfo $uploadInfo */
			$uploadInfo = $getInfoResult->getDataKey('response');

			// we need to tell the client where which file was saved to
			$files[$format] = $uploadInfo->getName();

			$uploadResult =
				(new Request\File\Upload\UploadFile($uploadInfo, $filePath, $fileSize, $job->backUrl))
					->setLoggerFluently($this->createHttpLogger($job))
					->send()
			;
			if (!$uploadResult->isSuccess())
			{
				$result->addErrors($uploadResult->getErrors());
			}
		}

		$result->setDataKey('files', $files);

		return $result;
	}

	private function createHttpLogger(Job $job): LoggerInterface
	{
		return $this->loggerFactory->create(
			Resolver::getCurrent(),
			[
				'type' => 'http',
				'guid' => $job->guid,
			]
		);
	}

	private function createCommandLogger(Job $job): LoggerInterface
	{
		return $this->loggerFactory->create(
			Resolver::getCurrent(),
			[
				'type' => 'transformation',
				'guid' => $job->guid,
			]
		);
	}

	private function getFirstCriticalError(Result $jobResult): ?Error
	{
		foreach ($jobResult->getErrors() as $error)
		{
			if (!($error instanceof Error\NotCritical))
			{
				return $error;
			}
		}

		return null;
	}
}
