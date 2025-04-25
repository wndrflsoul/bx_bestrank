<?php

namespace Bitrix\TransformerController\Daemon\Http\Client\Curl\RequestSender;

use Bitrix\TransformerController\Daemon\Http\Psr\Stream;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractRequestSender
{
	private array $responseHeaderLines = [];
	private ?ResponseInterface $response = null;
	private int $bytesWrittenToBody = 0;

	public function __construct(
		private readonly \CurlHandle $handle,
		private readonly array $curlOptions,
		private readonly ?\Closure $shouldFetchBodyCallback,
		private readonly ?int $bodyLengthMax,
		private readonly ?string $saveToFilePath = null,
		private readonly mixed $logFileResource = null,
	)
	{
	}

	public function send(): ResponseInterface
	{
		try
		{
			curl_setopt_array($this->handle, $this->getCurlOptions());

			curl_exec($this->handle);

			$this->checkCurlError();

			if (!$this->response)
			{
				// it seems there was no response body and the response is not built yet

				$this->buildResponse();
			}

			return $this->response;
		}
		finally
		{
			curl_reset($this->handle);
		}
	}

	private function getCurlOptions(): array
	{
		$options = $this->curlOptions + [
			CURLOPT_HEADERFUNCTION => $this->receiveHeaders(...),
			CURLOPT_WRITEFUNCTION => $this->receiveBody(...),
		];

		if ($this->logFileResource)
		{
			$options[CURLOPT_STDERR] = $this->logFileResource;
			$options[CURLOPT_VERBOSE] = true;
		}

		return $this->modifyCurlOptionsBeforeRequest($options);
	}

	abstract protected function modifyCurlOptionsBeforeRequest(array $curlOptions): array;

	private function receiveHeaders(\CurlHandle $handle, mixed $headerLine): int
	{
		if ($headerLine === "\r\n")
		{
			// headers ended

			return strlen($headerLine);
		}

		[$headerName, $headerValue] = $this->separateHeaderNameAndValue($headerLine);
		if (isset($headerName) && strtolower($headerName) === 'location')
		{
			// value is redirect url
			$this->onBeforeRedirect((string)$headerValue);
		}

		if ($this->isProtocolLine($headerLine))
		{
			$this->onAfterConnected($handle);

			if (!empty($this->responseHeaderLines))
			{
				// we have been redirected - curl gives us all headers, including for intermediate 3xx responses
				// since we don't care about them - just throw them away
				$this->responseHeaderLines = [];
			}
		}

		$this->responseHeaderLines[] = $headerLine;

		return strlen($headerLine);
	}

	/**
	 * This hook is called when we receive a Location header in a response.
	 * Throw an exception if you don't follow this redirect.
	 */
	abstract protected function onBeforeRedirect(string $redirectedToUrl): void;

	/**
	 * This hook is called just after curl has connected to a remote server, and we've received the first header line of
	 * a response.
	 *
	 * Throw an exception if you want to finish this request before reading anything else.
	 */
	abstract protected function onAfterConnected(\CurlHandle $handle): void;

	private function receiveBody(\CurlHandle $handle, mixed $data): int|false
	{
		if (!$this->response)
		{
			// we've received all headers and starting to read the body
			$this->buildResponse();

			if ($this->shouldFetchBodyCallback)
			{
				$shouldFetchBody = call_user_func($this->shouldFetchBodyCallback, $this->response);
				if (!$shouldFetchBody)
				{
					return false;
				}
			}
		}

		$bytesWritten = $this->response->getBody()->write($data);

		$this->bytesWrittenToBody += $bytesWritten;

		if ($this->bodyLengthMax > 0 && $this->bytesWrittenToBody > $this->bodyLengthMax)
		{
			return false;
		}

		return $bytesWritten;
	}

	private function buildResponse(): void
	{
		$protocolLine = array_shift($this->responseHeaderLines);

		if (!preg_match('#^HTTP/(\S+) (\d+) *(.*)#', $protocolLine, $find))
		{
			throw new class("Protocol line in http response doesnt match regex: {$protocolLine}")
				extends \RuntimeException
				implements ClientExceptionInterface {}
			;
		}

		$resource = $this->saveToFilePath ? fopen($this->saveToFilePath, 'w+') : fopen('php://temp', 'w+');

		$this->response = new \Bitrix\TransformerController\Daemon\Http\Psr\Response(
			$find[2],
			trim($find[3]),
			$this->parseHeaders($this->responseHeaderLines),
			new Stream($resource),
		);

		if ($this->response->hasHeader('Content-Encoding'))
		{
			// curl decodes stream only if 'Accept-Encoding' header was sent by us
			// curl wont decode stream if a remote server pushed compression to us without asking

			$resource = $this->response->getBody()->detach();

			// ZLIB or GZIP: use window=$W+32 for automatic header detection, so that both the formats can be recognized and decompressed; window=15+32=47 is the safer choice.
			stream_filter_append($resource, 'zlib.inflate', STREAM_FILTER_WRITE, ['window' => 47]);

			$this->response = $this->response->withBody(new Stream($resource));
		}
	}

	private function isProtocolLine(string $headerLine): bool
	{
		// I believe it should be a bit faster than regex since we don't need matches
		return str_starts_with($headerLine, 'HTTP/');
	}

	private function parseHeaders(array $headerLines): array
	{
		$result = [];

		foreach ($headerLines as $line)
		{
			[$name, $value] = $this->separateHeaderNameAndValue($line);

			if (isset($name, $value))
			{
				$result[$name] = $value;
			}
		}

		return $result;
	}

	private function separateHeaderNameAndValue(string $headerLine): array
	{
		$parts = explode(':', $headerLine, 2);
		if (count($parts) < 2)
		{
			return [null, null];
		}

		return [trim($parts[0]), trim($parts[1])];
	}

	private function checkCurlError(): void
	{
		$errorCode = curl_errno($this->handle);

		if ($errorCode === \CURLE_OK)
		{
			return;
		}

		throw new class(curl_error($this->handle), $errorCode)
			extends \Exception
			implements ClientExceptionInterface
			{}
		;
	}
}
