<?php

namespace Bitrix\TransformerController\Daemon\Http\Psr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class Response extends Message implements ResponseInterface
{
	public function __construct(
		private int $statusCode,
		private string $reasonPhrase = '',

		array $headers = [],
		?StreamInterface $body = null,
	)
	{
		parent::__construct($body ?? new Stream('php://temp'), $headers);
	}

	/**
	 * @inheritDoc
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	/**
	 * @inheritDoc
	 */
	public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
	{
		$clone = clone $this;

		$clone->statusCode = $code;
		$clone->reasonPhrase = $reasonPhrase;

		return $clone;
	}

	public function getReasonPhrase(): string
	{
		return $this->reasonPhrase;
	}
}
