<?php

namespace Bitrix\TransformerController\Daemon\File;

use Bitrix\TransformerController\Daemon\File\Type\Slug;

abstract class Type
{
	public function getMaxFileSize(): int
	{
		return 104_857_600; // ~100 mb
	}

	/**
	 * @return string[]
	 */
	abstract public function getAvailableFormats(): array;

	abstract public function getSlug(): Slug;
}
