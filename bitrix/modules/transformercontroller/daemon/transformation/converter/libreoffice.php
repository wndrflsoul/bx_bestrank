<?php

namespace Bitrix\TransformerController\Daemon\Transformation\Converter;

use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Result;
use Bitrix\TransformerController\Daemon\Shell\Timeout;
use Bitrix\TransformerController\Daemon\Transformation\Converter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class Libreoffice implements Converter, LoggerAwareInterface
{
	use LoggerAwareTrait;

	private const COMMAND_TEMPLATE = '#LIBREOFFICE_PATH# -env:UserInstallation=file://#ENVDIR# --convert-to #FORMAT# --outdir #WORK_DIR# #FILE# --headless --display :0 2>&1';

	public function __construct(
		private readonly Config $config,
	)
	{
		$this->logger ??= LoggerFactory::getInstance()->createNullLogger();
	}

	public function getAvailableFormats(): array
	{
		return ['pdf', 'txt', 'text', 'csv', 'jpg'];
	}

	public function convert(array $formats, string $filePath, int $fileSize): Result
	{
		if (array_diff($formats, $this->getAvailableFormats()))
		{
			throw new \InvalidArgumentException('Argument contains unknown formats: ' . implode(', ', $formats));
		}

		$timeout = Timeout::chooseTimeout($fileSize, $this->config->libreofficeTimeouts);

		$formatsWithoutText = array_diff($formats, ['text']);

		$result = new Result();
		$files = [];
		foreach ($formatsWithoutText as $format)
		{
			$resultFile = $this->convertSingle($filePath, $format, $timeout, $result);
			if ($resultFile)
			{
				$files[$format] = $resultFile;
			}
		}

		if (in_array('text', $formats, true))
		{
			$txtFilePath = $files['txt'] ?? $this->convertSingle($filePath, 'txt', $timeout, $result);
			if ($txtFilePath)
			{
				$result->setDataKey('text', file_get_contents($txtFilePath));
			}
		}

		return $result->setDataKey('files', $files);
	}

	private function convertSingle(string $filePath, string $format, int $timeout, Result $result): ?string
	{
		$command = str_replace(
			[
				'#LIBREOFFICE_PATH#',
				'#ENVDIR#',
				'#FORMAT#',
				'#WORK_DIR#',
				'#FILE#',
			],
			[
				escapeshellcmd($this->config->libreofficePath),
				$this->getLibreofficeConfigPath(),
				escapeshellarg($format),
				escapeshellarg(dirname($filePath)),
				escapeshellarg($filePath),
			],
			self::COMMAND_TEMPLATE
		);

		$command = Timeout::wrapCommandInTimeout($command, $timeout);

		// libreoffice may leave lock file on disk if it exists abruptly
		DeleteQueue::getInstance()->add($this->getPossibleDocumentLockFileName($filePath));

		$output = false;
		exec($command, $output, $exitCode);

		if (Timeout::isTimeoutExitCode($exitCode))
		{
			$this->logger->error(
				'Libreoffice timed out with exit code {exitCode}. Process was {timeoutType}',
				[
					'command' => $command,
					'filePath' => $filePath,
					'exitCode' => $exitCode,
					'timeout' => $timeout,
					'timeoutType' => Timeout::isTimeoutKillExitCode($exitCode) ? 'killed' : 'terminated',
					'output' => $output,
					'type' => 'libreoffice'
				]
			);

			$result->addError(
				new Error\NotCritical(
					"Transformation to {$format} timed out",
					Error\Dictionary::TRANSFORMATION_TIMED_OUT,
				),
			);

			return null;
		}

		if ($output === false)
		{
			$this->logger->error(
				'Exec libreoffice failed with exit code {exitCode} - we didnt get the output',
				[
					'command' => $command,
					'filePath' => $filePath,
					'exitCode' => $exitCode,
					'type' => 'libreoffice'
				]
			);

			$result->addError(
				new Error\NotCritical(
					"Transformation to {$format} failed",
					Error\Dictionary::TRANSFORMATION_FAILED,
				),
			);

			return null;
		}

		$resultFilePath = $this->extractFileNameFromLibreofficeOutput($output, $format);
		if (!$resultFilePath)
		{
			$this->logger->error(
				'Cant find {format} in libreoffice exec result',
				[
					'format' => $format,
					'type' => 'libreoffice',
					'filePath' => $filePath,
					'output' => $output,
				]
			);

			$result->addError(
				new Error\NotCritical("Transformation to {$format} failed", Error\Dictionary::TRANSFORMATION_FAILED),
			);

			return null;
		}

		DeleteQueue::getInstance()->add($resultFilePath);

		if (!file_exists($resultFilePath))
		{
			$this->logger->error(
				'Found file {resultFilePath} in libreoffice output, but it doesnt exist on disk',
				[
					'format' => $format,
					'type' => 'libreoffice',
					'filePath' => $filePath,
					'resultFilePath'=> $resultFilePath,
					'output' => $output,
				]
			);

			$result->addError(
				new Error\NotCritical("Transformation to {$format} failed", Error\Dictionary::TRANSFORMATION_FAILED),
			);

			return null;
		}

		return $resultFilePath;
	}

	private function getLibreofficeConfigPath(): string
	{
		//todo get temp dir from config
		return sys_get_temp_dir() . '/libreoffice-' . getmypid();
	}

	private function getPossibleDocumentLockFileName(string $filePath): string
	{
		$directory = dirname($filePath);
		$name = basename($filePath);

		return "{$directory}/~lock{$name}#";
	}

	private function extractFileNameFromLibreofficeOutput(array $output, string $format): ?string
	{
		foreach ($output as $line)
		{
			if (
				preg_match("#(/[ _a-zA-Z0-9./]+{$format})#U", $line, $matches)
				&& isset($matches[0])
				&& is_string($matches[0])
			)
			{
				return $matches[0];
			}
		}

		return null;
	}
}
