<?php

namespace Bitrix\TransformerController\Daemon\Http\Response\File\Upload;

use Bitrix\TransformerController\Daemon\Config\Resolver;

final class GetInfo
{
	public function __construct(
		private readonly array $decodedJson,
	)
	{
	}

	public function getChunkSize(): int
	{
		$sizeFromResponse = $this->decodedJson['chunk_size'] ?? null;
		$maxChunkSize = $this->getMaxChunkSize();
		if (!$sizeFromResponse || (int)$sizeFromResponse > $maxChunkSize)
		{
			return $maxChunkSize;
		}

		return (int)$sizeFromResponse;
	}

	public function getBucket(): int
	{
		return (int)($this->decodedJson['bucket'] ?? 0);
	}

	public function getName(): ?string
	{
		$name = $this->decodedJson['name'] ?? null;
		if (is_string($name))
		{
			return $name;
		}

		return null;
	}

	public function isSendChunkAsBinaryString(): bool
	{
		return !isset($this->decodedJson['upload_type']) || $this->decodedJson['upload_type'] !== 'file';
	}

	private function getMaxChunkSize(): int
	{
		return Resolver::getCurrent()->maxUploadChunkSize;
	}
}
