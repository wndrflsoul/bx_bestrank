<?php

namespace Bitrix\TransformerController\Daemon\Http\Psr;

use Psr\Http\Message\UriInterface;

final class Uri implements UriInterface
{
	private const MIN_PORT = 0;
	private const MAX_PORT = 65535;

	private string $scheme = '';
	private string $host = '';
	private ?int $port = null;
	private string $user = '';
	private string $pass = '';
	private string $path = '';
	private string $query = '';
	private string $fragment = '';

	public function __construct(string $uri = '')
	{
		$parsedUri = parse_url($uri);

		if ($parsedUri !== false)
		{
			$this->scheme = strtolower($parsedUri['scheme'] ?? $this->scheme);
			$this->host = strtolower($parsedUri['host'] ?? $this->host);
			$this->port = $parsedUri['port'] ?? $this->port;
			$this->user = $parsedUri['user'] ?? $this->user;
			$this->pass = $parsedUri['pass'] ?? $this->pass;
			$this->path = $parsedUri['path'] ?? $this->path;
			$this->query = $parsedUri['query'] ?? $this->query;
			$this->fragment = $parsedUri['fragment'] ?? $this->fragment;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getScheme(): string
	{
		return $this->scheme;
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthority(): string
	{
		if (!$this->host)
		{
			return '';
		}

		$authority = $this->host;

		$userInfo = $this->getUserInfo();
		if (!empty($userInfo))
		{
			$authority = $userInfo . '@' . $authority;
		}

		$port = $this->getPort();
		if ($port !== null)
		{
			$authority .= ':' . $port;
		}

		return $authority;
	}

	/**
	 * @inheritDoc
	 */
	public function getUserInfo(): string
	{
		if (!$this->user)
		{
			return '';
		}

		$info = $this->user;
		if ($this->pass)
		{
			$info .= ':' . $this->pass;
		}

		return $info;
	}

	/**
	 * @inheritDoc
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @inheritDoc
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}

	/**
	 * @inheritDoc
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @inheritDoc
	 */
	public function getQuery(): string
	{
		return $this->query;
	}

	/**
	 * @inheritDoc
	 */
	public function getFragment(): string
	{
		return $this->fragment;
	}

	/**
	 * @inheritDoc
	 */
	public function withScheme(string $scheme): UriInterface
	{
		$lowerScheme = strtolower($scheme);
		if ($lowerScheme !== 'http' && $lowerScheme !== 'https' && $lowerScheme !== '')
		{
			throw new \InvalidArgumentException('Unknown scheme ' . $scheme);
		}

		$clone = clone $this;
		$clone->scheme = $lowerScheme;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withUserInfo(string $user, ?string $password = null): UriInterface
	{
		$clone = clone $this;

		$clone->user = $user;
		if ($password !== null)
		{
			$clone->pass = $password;
		}

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withHost(string $host): UriInterface
	{
		$clone = clone $this;

		$clone->host = strtolower($host);

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withPort(?int $port): UriInterface
	{
		if ($port !== null && ($port < self::MIN_PORT || $port > self::MAX_PORT))
		{
			throw new \InvalidArgumentException('Unknown port value ' . $port);
		}

		$clone = clone $this;

		$clone->port = $port;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withPath(string $path): UriInterface
	{
		$clone = clone $this;

		$clone->path = $path;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withQuery(string $query): UriInterface
	{
		$clone = clone $this;

		$clone->query = $query;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withFragment(string $fragment): UriInterface
	{
		$clone = clone $this;

		$clone->fragment = $fragment;

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString(): string
	{
		$uri = '';

		$authority = $this->getAuthority();
		if ($authority !== '')
		{
			$uri .= $authority;
		}

		$scheme = $this->getScheme();
		if ($scheme !== '' && !empty($authority))
		{
			$uri = $scheme . '://' . $uri;
		}

		$path = $this->getPath();
		if ($path)
		{
			$uri = rtrim($uri, '/') . '/' . ltrim($path, '/');
		}

		$query = $this->getQuery();
		if ($query)
		{
			$uri .= '?' . $query;
		}

		$fragment = $this->getFragment();
		if ($fragment)
		{
			$uri .= "#{$fragment}";
		}

		return $uri;
	}
}
