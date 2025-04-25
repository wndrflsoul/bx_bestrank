<?php

namespace Bitrix\TransformerController\Daemon\File\Type;

use Bitrix\TransformerController\Daemon\File\Type;

final class Video extends Type
{
	public function __construct(
		private readonly string $tarif
	)
	{
	}

	public function getAvailableFormats(): array
	{
		return [
			'mp4',
			'sha1',
			'crc32',
			'md5',
			'jpg',
		];
	}

	public function getMaxFileSize(): int
	{
		if($this->tarif === 'B24_PROJECT')
		{
			return 314_572_800; // ~ 300 mb
		}

		return 3_221_225_472; // ~ 3 gb
	}

	public function getSlug(): Slug
	{
		return Slug::VIDEO;
	}
}
