<?php

namespace Bitrix\TransformerController\Daemon\Transformation\Converter;

use Bitrix\TransformerController\Daemon\Result;
use Bitrix\TransformerController\Daemon\Transformation\Converter;

final class Crc32 implements Converter
{
	public function convert(array $formats, string $filePath, int $fileSize): Result
	{
		if ($formats !== $this->getAvailableFormats())
		{
			throw new \InvalidArgumentException('Unknown formats: ' . implode(', ', $formats));
		}

		$hash = crc32(file_get_contents($filePath));

		return (new Result())->setDataKey('crc32', $hash);
	}

	public function getAvailableFormats(): array
	{
		return ['crc32'];
	}
}
