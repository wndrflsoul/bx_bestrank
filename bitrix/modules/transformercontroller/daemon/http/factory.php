<?php

namespace Bitrix\TransformerController\Daemon\Http;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Traits\Singleton;
use Psr\Http\Message\UriInterface;

final class Factory
{
	use Singleton;

	private ?Client\ClientInterface $client = null;

	public function getClient(): Client\ClientInterface
	{
		if (!$this->client)
		{
			$config = Resolver::getCurrent();

			$this->client = new Client\Curl([
				'headers' => [
					'User-Agent' => 'Bitrix Transformer Server',
				],
				'socketTimeout' => $config->defaultSocketTimeout,
				'streamTimeout' => $config->defaultStreamTimeout,
				'debug' => $config->isWriteHttpDebugLog,
				'logFilePath' => $config->httpLogFilePath,
				'maxRedirects' => $config->maxRedirects,
				'privateIp' => $config->isPrivateIpAllowed,

				//persistent connections
				'isKeepConnectionAlive' => $config->isKeepConnectionAlive,
				'connectionMaxIdleTime' => $config->connectionMaxIdleTime,
				'connectionMaxLifeTime' => $config->connectionMaxLifeTime,
				'maxAliveConnections' => $config->maxAliveConnections,
			]);
		}

		return $this->client;
	}

	/**
	 * PSR-17 compatible factory method
	 *
	 * Create a new URI.
	 *
	 * @param string $uri The URI to parse.
	 *
	 * @throws \InvalidArgumentException If the given URI cannot be parsed.
	 */
	public function createUri(string $uri = '') : UriInterface
	{
		return new Psr\Uri($uri);
	}
}
