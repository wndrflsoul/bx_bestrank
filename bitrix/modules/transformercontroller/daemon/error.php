<?php

namespace Bitrix\TransformerController\Daemon;

use Throwable;

class Error implements \JsonSerializable
{
	public function __construct(
		private readonly string $message,
		private readonly int $code = 0,
		private readonly array $customData = []
	)
	{
	}

	/**
	 * @param Throwable $exception
	 * @return self
	 */
	public static function createFromThrowable(Throwable $exception): self
	{
		return new self($exception->getMessage(), $exception->getCode());
	}

	public function getCode(): int
	{
		return $this->code;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getCustomData(): array
	{
		return $this->customData;
	}

	public function __toString(): string
	{
		return $this->getMessage();
	}

	/**
	 * Used only for debug output
	 */
	public function jsonSerialize(): array
	{
		return [
			'code' => $this->code,
			'message' => $this->message,
			'customData' => $this->customData,
		];
	}
}
