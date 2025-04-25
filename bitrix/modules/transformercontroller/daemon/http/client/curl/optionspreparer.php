<?php

namespace Bitrix\TransformerController\Daemon\Http\Client\Curl;

use Psr\Http\Message\UriInterface;

final class OptionsPreparer
{
	public function __construct(
		private readonly array $defaultOptions
	)
	{
	}

	public function prepareCurlOptions(string $method, string|UriInterface $uri, array $options): array
	{
		$curlOptions = [
			CURLOPT_URL => (string)$uri,
			// security hardening - only http/s proto, even when following redirects
			CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => $this->getOption($options, 'maxRedirects'),
			CURLOPT_CONNECTTIMEOUT => $this->getOption($options, 'socketTimeout'),
			CURLOPT_LOW_SPEED_TIME => $this->getOption($options, 'streamTimeout'),
			CURLOPT_LOW_SPEED_LIMIT => 1, // bytes/sec
		];

		if ($this->getOption($options, 'isKeepConnectionAlive'))
		{
			$curlOptions[CURLOPT_MAXCONNECTS] = $this->getOption($options, 'maxAliveConnections');
			if (defined('CURLOPT_MAXAGE_CONN'))
			{
				// php 8.2 only
				$curlOptions[CURLOPT_MAXAGE_CONN] = $this->getOption($options, 'connectionMaxIdleTime');
			}
			if (defined('CURLOPT_MAXLIFETIME_CONN'))
			{
				// php 8.2 only
				$curlOptions[CURLOPT_MAXLIFETIME_CONN] = $this->getOption($options, 'connectionMaxLifeTime');
			}
		}
		else
		{
			$curlOptions[CURLOPT_FORBID_REUSE] = true;
			$curlOptions[CURLOPT_FRESH_CONNECT] = true;
		}

		$bodyLengthMax = $this->getOption($options, 'bodyLengthMax');

		if ($bodyLengthMax > 0)
		{
			if (defined('CURLOPT_MAXFILESIZE_LARGE'))
			{
				$curlOptions[CURLOPT_MAXFILESIZE_LARGE] = $bodyLengthMax;
			}
			elseif ($bodyLengthMax <= 2 * 1024 * 1024 * 1024)
			{
				// max value for CURLOPT_MAXFILESIZE is 2 gb
				// don't set option on files > 2 gb, or they may be aborted
				$curlOptions[CURLOPT_MAXFILESIZE] = $bodyLengthMax;
			}
		}

		if ($method === 'HEAD')
		{
			$curlOptions[CURLOPT_NOBODY] = true;
		}
		else
		{
			$curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
		}

		if ($method !== 'GET' && $method !== 'HEAD' && $method !== 'TRACE')
		{
			$json = $this->getOption($options, 'json');
			$form = $this->getOption($options, 'form');
			$multipart = $this->getOption($options, 'multipart');
			if (is_array($json) && !empty($json))
			{
				$options['headers']['Content-Type'] = 'application/json';
				$curlOptions[CURLOPT_POSTFIELDS] = json_encode($json, JSON_THROW_ON_ERROR);
			}
			if (is_array($form) && !empty($form))
			{
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($form, '', '&');
			}
			elseif (is_array($multipart) && !empty($multipart))
			{
				$curlOptions[CURLOPT_POSTFIELDS] = $this->prepareMultipart($multipart);
			}
		}

		$curlOptions[CURLOPT_HTTPHEADER] = $this->buildHeaders($options);

		return $curlOptions;
	}

	public function getOption(array $options, string $key): mixed
	{
		return $options[$key] ?? $this->defaultOptions[$key] ?? null;
	}

	private function buildHeaders(array $options): array
	{
		$allHeaders = ($options['headers'] ?? []) + ($this->defaultOptions['headers'] ?? []);

		if (!isset($allHeaders['Expect']))
		{
			// curl aggressively adds 'Except: 100-continue' to request even if you didn't ask for it
			$allHeaders['Expect'] = '';
		}

		if (!isset($allHeaders['Connection']))
		{
			$allHeaders['Connection'] = $this->getOption($options, 'isKeepConnectionAlive') ? 'keep-alive' : 'close';
		}

		$result = [];

		foreach ($allHeaders as $headerName => $values)
		{
			$result[] = "{$headerName}: " . implode(', ', (array)$values);
		}

		return $result;
	}

	private function prepareMultipart(array $multipartFromOptions): array
	{
		foreach ($multipartFromOptions as $name => $value)
		{
			if (
				is_array($value)
				&& isset($value['content'])
			)
			{
				$multipartFromOptions[$name] = new \CURLStringFile($value['content'], $value['filename'] ?? $name);
			}
		}

		return $multipartFromOptions;
	}
}
