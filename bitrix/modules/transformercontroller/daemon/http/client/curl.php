<?php

namespace Bitrix\TransformerController\Daemon\Http\Client;

use Bitrix\TransformerController\Daemon\Http\Client\Curl\OptionsPreparer;
use Bitrix\TransformerController\Daemon\Http\Client\Curl\RequestSender\AbstractRequestSender;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Curl implements ClientInterface
{
	private readonly \CurlHandle $handle;
	private readonly OptionsPreparer $optionsPreparer;

	public function __construct(
		private readonly array $defaultOptions = [],
	)
	{
		if (!function_exists('curl_init'))
		{
			throw new \RuntimeException('Curl is not installed');
		}

		$this->handle = curl_init();
		$this->optionsPreparer = new OptionsPreparer($this->defaultOptions);
	}

	/**
	 * @inheritDoc
	 */
	public function get(UriInterface|string $uri, array $options = []): ResponseInterface
	{
		$curlOptions = $this->optionsPreparer->prepareCurlOptions(
			'GET',
			$uri,
			$options,
		);

		$logFileResource = $this->getLogFileResource($options);

		$sender = $this->createRequestSender(
			$curlOptions,
			null,
			$options,
			$logFileResource,
		);

		try
		{
			return $sender->send();
		}
		finally
		{
			if ($logFileResource)
			{
				fclose($logFileResource);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function download(UriInterface|string $uri, string $saveToFilePath, array $options = []): ResponseInterface
	{
		$curlOptions = $this->optionsPreparer->prepareCurlOptions(
			'GET',
			$uri,
			$options,
		);

		$logFileResource = $this->getLogFileResource($options);

		$sender = $this->createRequestSender(
			$curlOptions,
			$saveToFilePath,
			$options,
			$logFileResource,
		);

		try
		{
			return $sender->send();
		}
		finally
		{
			if ($logFileResource)
			{
				fclose($logFileResource);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function post(UriInterface|string $uri, array $options = []): ResponseInterface
	{
		$curlOptions = $this->optionsPreparer->prepareCurlOptions(
			'POST',
			$uri,
			$options,
		);

		$logFileResource = $this->getLogFileResource($options);

		$sender = $this->createRequestSender(
			$curlOptions,
			null,
			$options,
			$logFileResource,
		);

		try
		{
			return $sender->send();
		}
		finally
		{
			if ($logFileResource)
			{
				fclose($logFileResource);
			}
		}
	}

	private function getLogFileResource(array $options): mixed
	{
		$logFilePath = $this->optionsPreparer->getOption($options, 'logFilePath');
		$isDebug = $this->optionsPreparer->getOption($options, 'debug');
		if ($isDebug && $logFilePath)
		{
			return fopen($logFilePath, 'a+');
		}

		return null;
	}

	private function createRequestSender(
		array $curlOptions,
		?string $saveToFilePath,
		array $options,
		mixed $logFileResource,
	): AbstractRequestSender
	{
		$shouldFetchBody = $this->optionsPreparer->getOption($options, 'shouldFetchBody');
		$bodyLengthMax = $this->optionsPreparer->getOption($options, 'bodyLengthMax');

		if ($this->optionsPreparer->getOption($options, 'privateIp'))
		{
			return new Curl\RequestSender\Regular(
				$this->handle,
				$curlOptions,
				$shouldFetchBody,
				$bodyLengthMax,
				$saveToFilePath,
				$logFileResource,
			);
		}
		else
		{
			return new Curl\RequestSender\OnlyPublicNetwork(
				$this->handle,
				$curlOptions,
				$shouldFetchBody,
				$bodyLengthMax,
				$saveToFilePath,
				$logFileResource,
			);
		}
	}
}
