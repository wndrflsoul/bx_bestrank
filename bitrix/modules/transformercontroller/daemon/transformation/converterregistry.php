<?php

namespace Bitrix\TransformerController\Daemon\Transformation;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\File\Type;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Crc32;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Ffmpeg;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Http;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Libreoffice;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Md5;
use Bitrix\TransformerController\Daemon\Transformation\Converter\Sha1;

final class ConverterRegistry
{
	private array $universalConverters;
	private array $typedConverters;

	public function __construct()
	{
		$config = Resolver::getCurrent();

		// converters should be listed by their priority - first converters are called first
		// it may be important if different converters can handle the same formats

		$this->universalConverters = [
			new Md5(),
			new Sha1(),
			new Crc32(),
		];

		$this->typedConverters = [
			Type\Slug::DOCUMENT->value => [
				$config->isUseHttpForLibreoffice ? new Http\Libreoffice($config) : new Libreoffice($config),
			],
			Type\Slug::VIDEO->value => [
				$config->isUseHttpForFfmpeg ? new Http\Ffmpeg($config) : new Ffmpeg($config),
			],
		];
	}

	/**
	 * @param Type\Slug $fileTypeSlug
	 *
	 * @return Converter[]
	 */
	public function getConverters(Type\Slug $fileTypeSlug): array
	{
		return array_merge($this->typedConverters[$fileTypeSlug->value], $this->universalConverters);
	}
}
