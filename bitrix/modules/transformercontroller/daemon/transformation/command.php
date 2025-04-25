<?php

namespace Bitrix\TransformerController\Daemon\Transformation;

use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\File\Type;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class Command
{
	public function __construct(
		private readonly ConverterRegistry $converterRegistry,
		private readonly Type $fileType,
		private readonly array $formats,
		private readonly ?string $filePath,
		private readonly ?int $fileSize,
		private readonly LoggerInterface $logger,
	)
	{
	}

	public function execute(): Result
	{
		if (empty($this->filePath))
		{
			$this->logger->critical(
				'Received job without file url. In theory its possible.'
				. ' But in practice we have no commands that dont require file as an argument.'
				. ' Something is seriously wrong if you see this',
				[
					'formats' => $this->formats,
					'fileType' => $this->fileType->getSlug(),
					'filePath' => $this->filePath,
					'fileSize' => $this->fileSize,
				]
			);

			return (new Result())->addError(new Error('Command failed: no file url', Error\Dictionary::COMMAND_ERROR));
		}

		if ($this->fileSize === null)
		{
			$this->logger->critical(
				'Received job with file url, but file size is null. Something is seriously wrong if you see this',
				[
					'formats' => $this->formats,
					'fileType' => $this->fileType->getSlug(),
					'filePath' => $this->filePath,
					'fileSize' => $this->fileSize,
				]
			);

			throw new \InvalidArgumentException('File path is provided, but file size is null');
		}

		$converters = $this->converterRegistry->getConverters($this->fileType->getSlug());
		$formatsToProcess = $this->getFormatsToProcess();

		$result = new Result();
		$remainingFormats = $formatsToProcess;
		$data = [];
		foreach ($converters as $converter)
		{
			if (empty($remainingFormats))
			{
				break;
			}

			$formatsForThisConverter = array_intersect($converter->getAvailableFormats(), $remainingFormats);
			if (empty($formatsForThisConverter))
			{
				// this converter doesn't have formats that we need
				continue;
			}
			// remove already handled formats
			$remainingFormats = array_diff($remainingFormats, $formatsForThisConverter);

			if ($converter instanceof LoggerAwareInterface)
			{
				$converter->setLogger($this->logger);
			}

			$singleResult = $converter->convert(
				array_values($formatsForThisConverter),
				$this->filePath,
				$this->fileSize,
			);
			if (!$singleResult->isSuccess())
			{
				$result->addErrors($singleResult->getErrors());
			}

			if ($singleResult->getDataKey('files'))
			{
				$data['files'] ??= [];
				$data['files'] += $singleResult->getDataKey('files');
			}

			$data += $singleResult->getData();
		}

		if (!empty($remainingFormats))
		{
			$this->logger->critical(
				'There are formats that should be supported, but no converter for them was found',
				[
					'unhandledFormats' => $remainingFormats,
					'formats' => $this->formats,
					'fileType' => $this->fileType->getSlug(),
					'filePath' => $this->filePath,
					'fileSize' => $this->fileSize,
				]
			);

			$result->addError(
				new Error\NotCritical(
					'Some formats were not handled',
					Error\Dictionary::COMMAND_ERROR,
				)
			);
		}

		$result->setData($data);

		if (!$this->isAtLeastOneFormatWasTransformed($result, $formatsToProcess))
		{
			$result->addError(
				new Error(
					'All transformation has failed',
					Error\Dictionary::COMMAND_FAILED,
				)
			);
		}

		return $result;
	}

	private function getFormatsToProcess(): array
	{
		return array_intersect($this->formats, $this->fileType->getAvailableFormats());
	}

	private function isAtLeastOneFormatWasTransformed(Result $result, array $formatsToProcess): bool
	{
		$allTransformedFormats = [...$result->getData(), ...($result->getDataKey('files') ?? [])];
		unset($allTransformedFormats['files']);

		foreach ($formatsToProcess as $format)
		{
			if (!empty($allTransformedFormats[$format]))
			{
				return true;
			}
		}

		return false;
	}
}
