<?php

namespace Bitrix\TransformerController\Daemon\Transformation\Converter\Http;

use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Http\Request\Convert;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Result;
use Bitrix\TransformerController\Daemon\Shell\Timeout;
use Bitrix\TransformerController\Daemon\Transformation\Converter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class Ffmpeg implements Converter, LoggerAwareInterface
{
	use LoggerAwareTrait;

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

		return (new Convert($this->config->ffmpegUrl, $filePath, $formats, $timeout))
			->setLoggerFluently($this->logger)
			->send()
		;
	}

	/**
	 * @inheritDoc
	 */
	public function getAvailableFormats(): array
	{
		return ['mp4', 'jpg'];
	}
}
