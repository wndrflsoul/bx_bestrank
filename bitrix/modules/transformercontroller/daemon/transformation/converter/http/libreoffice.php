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

final class Libreoffice implements Converter, LoggerAwareInterface
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

		$formatsWithoutText = array_diff($formats, ['text']);
		$isTextFormatNeeded = $formatsWithoutText !== $formats;
		if ($isTextFormatNeeded && !in_array('txt', $formatsWithoutText))
		{
			$formatsWithoutText[] = 'txt';
		}

		$timeout = Timeout::chooseTimeout($fileSize, $this->config->libreofficeTimeouts);

		$result = (new Convert($this->config->libreofficeUrl, $filePath, $formatsWithoutText, $timeout))
			->setLoggerFluently($this->logger)
			->send()
		;

		if ($isTextFormatNeeded)
		{
			$files = $result->getDataKey('files') ?? [];
			if (!empty($files['txt']))
			{
				$result->setDataKey('text', file_get_contents($files['txt']));
			}
		}

		// send to the client only those formats that were requested
		$filteredResultFiles = array_filter(
			$result->getDataKey('files') ?? [],
			fn(mixed $key) => in_array($key, $formats, true),
			ARRAY_FILTER_USE_KEY
		);

		return $result->setDataKey('files', $filteredResultFiles);
	}

	/**
	 * @inheritDoc
	 */
	public function getAvailableFormats(): array
	{
		return ['pdf', 'txt', 'text', 'csv', 'jpg'];
	}
}
