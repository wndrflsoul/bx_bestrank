<?php

namespace Bitrix\TransformerController\Daemon\Http\Client\Curl\RequestSender;

use Bitrix\TransformerController\Daemon\Http\Factory;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * If curl tries to make a request or connect to an IP in private/local network, it will be aborted.
 */
class OnlyPublicNetwork extends AbstractRequestSender
{
	private Factory $httpFactory;

	public function __construct(
		\CurlHandle $handle,
		array $curlOptions,
		?\Closure $shouldFetchBodyCallback,
		?int $bodyLengthMax,
		?string $saveToFilePath = null,
		mixed $logFileResource = null
	)
	{
		parent::__construct(
			$handle,
			$curlOptions,
			$shouldFetchBodyCallback,
			$bodyLengthMax,
			$saveToFilePath,
			$logFileResource
		);

		$this->httpFactory = Factory::getInstance();
	}

	protected function modifyCurlOptionsBeforeRequest(array $curlOptions): array
	{
		$uri = $this->httpFactory->createUri($curlOptions[CURLOPT_URL]);
		$ip = gethostbyname($uri->getHost());

		if ($this->isIpPrivate($ip))
		{
			$this->throwRequestAbortedException();
		}

		$defaultPort = $uri->getScheme() === 'https' ? 443 : 80;

		// force curl to connect to the IP we've just resolved and checked
		$curlOptions[CURLOPT_RESOLVE] = [
			// CURLOPT_RESOLVE manually adds the host-port-ip tuple to curl in-memory DNS cache
			// + signals to curl that this tuple should not be persistent and should expire as normal DNS cache would
			'+' . $uri->getHost() . ':' . ($uri->getPort() ?? $defaultPort) . ':' . $ip,
		];

		return $curlOptions;
	}

	private function isIpPrivate(string $ip): bool
	{
		$validationResult = filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);

		return ($validationResult === false);
	}

	private function throwRequestAbortedException(): never
	{
		throw new class('Requests to a private network are forbidden. Check your config if you want to enable it')
			extends \Exception
			implements ClientExceptionInterface
			{}
		;
	}

	protected function onBeforeRedirect(string $redirectedToUrl): void
	{
		$uri = $this->httpFactory->createUri($redirectedToUrl);
		$ip = gethostbyname($uri->getHost());

		if ($this->isIpPrivate($ip))
		{
			$this->throwRequestAbortedException();
		}
	}

	protected function onAfterConnected(\CurlHandle $handle): void
	{
		$ipWeAreConnectedTo = curl_getinfo($handle, CURLINFO_PRIMARY_IP);

		if (empty($ipWeAreConnectedTo) || $this->isIpPrivate($ipWeAreConnectedTo))
		{
			$this->throwRequestAbortedException();
		}
	}
}
