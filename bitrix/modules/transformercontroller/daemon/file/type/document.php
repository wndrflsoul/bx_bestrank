<?php

namespace Bitrix\TransformerController\Daemon\File\Type;

use Bitrix\TransformerController\Daemon\File\Type;

final class Document extends Type
{
	public function getAvailableFormats(): array
	{
		return [
			'pdf',
			'jpg',
			'txt',
			'text',
			'md5',
			'sha1',
			'crc32',
		];
	}

	public function getSlug(): Slug
	{
		return Slug::DOCUMENT;
	}
}
