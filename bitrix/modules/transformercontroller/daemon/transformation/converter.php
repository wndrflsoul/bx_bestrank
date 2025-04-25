<?php

namespace Bitrix\TransformerController\Daemon\Transformation;

use Bitrix\TransformerController\Daemon\Result;

interface Converter
{
	/**
	 * @param string[] $formats
	 * @param string $filePath
	 * @param int $fileSize
	 *
	 * @return Result key 'files' for file results - ['format' => 'result file path]. Other keys - for content results
	 */
	public function convert(array $formats, string $filePath, int $fileSize): Result;

	/**
	 * @return string[]
	 */
	public function getAvailableFormats(): array;
}
