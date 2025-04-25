<?php

namespace Bitrix\TransformerController\Daemon\Http\Psr;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
	private string $protocolVersion = '1.1';
	private array $headersMap = [];
	private array $headerLowerToOriginalMap = [];

	public function __construct(
		private StreamInterface $stream,
		array $headers = [],
	)
	{
		foreach ($headers as $name => $values)
		{
			if (!self::validateName($name))
			{
				throw new \InvalidArgumentException("Invalid header name {$name}");
			}

			foreach ((array)$values as $value)
			{
				if (!self::validateValue($value))
				{
					throw new \InvalidArgumentException("Invalid header value {$value}");
				}
			}

			$lowerName = strtolower($name);
			$this->headerLowerToOriginalMap[$lowerName] = $name;
			$this->headersMap[$name] = (array)$values;
		}
	}

	public function __clone(): void
	{
		$this->stream = clone $this->stream;
	}

	/**
	 * @inheritDoc
	 */
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	/**
	 * @inheritDoc
	 */
	public function withProtocolVersion(string $version): MessageInterface
	{
		$clone = clone $this;
		$clone->protocolVersion = $version;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaders(): array
	{
		return $this->headersMap;
	}

	/**
	 * @inheritDoc
	 */
	public function hasHeader(string $name): bool
	{
		return isset($this->headerLowerToOriginalMap[strtolower($name)]);
	}

	/**
	 * @inheritDoc
	 */
	public function getHeader(string $name): array
	{
		$lower = strtolower($name);

		$original = $this->headerLowerToOriginalMap[$lower] ?? null;
		if (!$original)
		{
			return [];
		}

		return $this->headersMap[$original] ?? [];
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaderLine(string $name): string
	{
		return implode(', ', $this->getHeader($name));
	}

	/**
	 * @inheritDoc
	 */
	public function withHeader(string $name, $value): MessageInterface
	{
		if (!self::validateName($name))
		{
			throw new \InvalidArgumentException("Invalid header name {$name}");
		}

		$value = (array)$value;

		foreach ($value as $single)
		{
			if (!self::validateValue($single))
			{
				throw new \InvalidArgumentException("Invalid header value {$single}");
			}
		}

		$clone = clone $this;

		$lower = strtolower($name);
		if (isset($clone->headerLowerToOriginalMap[$lower]))
		{
			$original = $clone->headerLowerToOriginalMap[$lower];
			unset($clone->headersMap[$original], $clone->headerLowerToOriginalMap[$lower]);
		}

		$clone->headerLowerToOriginalMap[$lower] = $name;
		$clone->headersMap[$name] = $value;

		return $clone;
	}

	/**
	 * @see https://tools.ietf.org/html/rfc7230#section-3.2
	 * field-name     = token
	 * token          = 1*tchar
	 * tchar          = "!" / "#" / "$" / "%" / "&" / "'" / "*"
	 *                  / "+" / "-" / "." / "^" / "_" / "`" / "|" / "~"
	 *                  / DIGIT / ALPHA
	 */
	private static function validateName(string $name): bool
	{
		return (!str_contains($name, "\0") && preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name));
	}

	/**
	 * @see https://tools.ietf.org/html/rfc7230#section-3.2
	 * field-value    = *( field-content / obs-fold )
	 * field-content  = field-vchar [ 1*( SP / HTAB ) field-vchar ]
	 * field-vchar    = VCHAR / obs-text
	 * VCHAR          = %x21-7E
	 * obs-text       = %x80-FF
	 */
	private static function validateValue(string $value): bool
	{
		return (!str_contains($value, "\0") && preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/', $value));
	}

	final protected function setHostHeader(string $host): self
	{
		$this->headersMap['Host'] = [$host];
		$this->headerLowerToOriginalMap[strtolower($host)] = 'Host';

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function withAddedHeader(string $name, $value): MessageInterface
	{
		if (!self::validateName($name))
		{
			throw new \InvalidArgumentException("Invalid header name {$name}");
		}

		$value = (array)$value;

		foreach ($value as $single)
		{
			if (!self::validateValue($single))
			{
				throw new \InvalidArgumentException("Invalid header value {$single}");
			}
		}

		$clone = clone $this;

		$lower = strtolower($name);

		$original = $clone->headerLowerToOriginalMap[$lower] ?? $name;
		$currentValues = $clone->headersMap[$original] ?? [];

		$newValues = [
			...$currentValues,
			...$value,
		];

		$clone->headersMap[$original] = $newValues;
		$clone->headerLowerToOriginalMap[$lower] = $original;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withoutHeader(string $name): MessageInterface
	{
		$clone = clone $this;

		$lower = strtolower($name);
		$original = $clone->headerLowerToOriginalMap[$lower] ?? null;

		unset(
			$clone->headersMap[$original],
			$clone->headerLowerToOriginalMap[$lower],
		);

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function getBody(): StreamInterface
	{
		return $this->stream;
	}

	/**
	 * @inheritDoc
	 */
	public function withBody(StreamInterface $body): MessageInterface
	{
		$clone = clone $this;

		$clone->stream = $body;

		return $clone;
	}
}
