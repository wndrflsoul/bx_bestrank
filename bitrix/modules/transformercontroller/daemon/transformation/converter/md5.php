<?php

namespace Bitrix\TransformerController\Daemon\Transformation\Converter;

use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Result;
use Bitrix\TransformerController\Daemon\Transformation\Converter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Md5 implements Converter, LoggerAwareInterface
{
	use LoggerAwareTrait;

	public function __construct()
	{
		$this->logger ??= LoggerFactory::getInstance()->createNullLogger();
	}

	public function convert(array $formats, string $filePath, int $fileSize): Result
	{
		if ($formats !== $this->getAvailableFormats())
		{
			throw new \InvalidArgumentException('Unknown formats: ' . implode(', ', $formats));
		}

		$result = new Result();

		$hash = md5_file($filePath);
		if ($hash === false)
		{
			$this->logger->error(
				'md5 hash failed',
				[
					'type' => 'md5',
					'filePath' => $filePath,
					'fileSize' => $fileSize,
				]
			);

			return $result->addError(new Error\NotCritical('md5 hash failed', Error\Dictionary::TRANSFORMATION_FAILED));
		}

		return $result->setDataKey('md5', $hash);
	}

	public function getAvailableFormats(): array
	{
		return ['md5'];
	}
}
