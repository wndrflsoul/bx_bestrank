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

final class Ffmpeg implements Converter, LoggerAwareInterface
{
	use LoggerAwareTrait;

	private const COMMAND_TEMPLATES = [
		'mp4' => '#FFMPEG_PATH# -nostdin -loglevel warning -i #FILE# -c:v libx264 -r 25 -vf scale=w="min(min(#MAX_WIDTH#\,trunc(#MAX_WIDTH#/max(a/1.7778\,1.7778/a)/2)*2)\,trunc(iw/2)*2):h=-2" -strict -2 -preset fast -pix_fmt yuv420p -codec:a aac -f mp4 #RESULT# 2>&1',
		'jpg' => '#FFMPEG_PATH# -nostdin -loglevel warning -i #FILE# -an -ss 00:00:00 -vf scale=w="min(min(#MAX_WIDTH#\,trunc(#MAX_WIDTH#/max(a/1.7778\,1.7778/a)/2)*2)\,trunc(iw/2)*2):h=-2" -vframes: 1 -r 1 -y #RESULT# 2>&1',
	];

	public function __construct(
		private readonly Config $config,
	)
	{
		$this->logger ??= LoggerFactory::getInstance()->createNullLogger();
	}

	/**
	 * @inheritDoc
	 */
	public function convert(array $formats, string $filePath, int $fileSize): Result
	{
		if (array_diff($formats, $this->getAvailableFormats()))
		{
			throw new \InvalidArgumentException('Argument contains unknown formats: ' . implode(', ', $formats));
		}

		$timeout = Timeout::chooseTimeout($fileSize, $this->config->ffmpegTimeouts);

		$result = new Result();
		$files = [];
		foreach ($formats as $format)
		{
			$resultFile = $this->convertSingle($filePath, $format, $timeout, $result);
			if ($resultFile)
			{
				$files[$format] = $resultFile;
			}
		}

		return $result->setDataKey('files', $files);
	}

	private function convertSingle(string $filePath, string $format, int $timeout, Result $result): ?string
	{
		$template = self::COMMAND_TEMPLATES[$format];

		$resultFilePath = dirname($filePath) . '/' . bin2hex(random_bytes(10)) . '.' . $format;
		DeleteQueue::getInstance()->add($resultFilePath);

		$command = str_replace(
			[
				'#FFMPEG_PATH#',
				'#FILE#',
				'#MAX_WIDTH#',
				'#RESULT#',
			],
			[
				escapeshellcmd($this->config->ffmpegPath),
				escapeshellarg($filePath),
				$this->config->ffmpegMaxWidth,
				escapeshellarg($resultFilePath),
			],
			$template,
		);

		$command = Timeout::wrapCommandInTimeout($command, $timeout);

		$output = false;
		exec($command, $output, $exitCode);

		if (Timeout::isTimeoutExitCode($exitCode))
		{
			$this->logger->error(
				'Ffmpeg timed out with exit code {exitCode}. Process was {timeoutType}',
				[
					'command' => $command,
					'filePath' => $filePath,
					'exitCode' => $exitCode,
					'timeout' => $timeout,
					'timeoutType' => Timeout::isTimeoutKillExitCode($exitCode) ? 'killed' : 'terminated',
					'output' => $output,
					'type' => 'ffmpeg'
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

		if (!file_exists($resultFilePath))
		{
			$this->logger->error(
				'Executed ffmpeg, but result file doesnt exist on disk. It seems that ffmpeg failed',
				[
					'format' => $format,
					'command' => $command,
					'type' => 'ffmpeg',
					'filePath' => $filePath,
					'resultFilePath'=> $resultFilePath,
					'output' => $output,
					'exitCode' => $exitCode,
				]
			);

			$result->addError(
				new Error\NotCritical("Transformation to {$format} failed", Error\Dictionary::TRANSFORMATION_FAILED),
			);

			return null;
		}

		return $resultFilePath;
	}

	/**
	 * @inheritDoc
	 */
	public function getAvailableFormats(): array
	{
		return ['mp4', 'jpg'];
	}
}
