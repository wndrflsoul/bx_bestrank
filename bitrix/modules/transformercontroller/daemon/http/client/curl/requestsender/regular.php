<?php

namespace Bitrix\TransformerController\Daemon\Http\Client\Curl\RequestSender;

/**
 * Just performs curl request, without interrupting it in any way.
 */
final class Regular extends AbstractRequestSender
{
	protected function modifyCurlOptionsBeforeRequest(array $curlOptions): array
	{
		return $curlOptions;
	}

	protected function onBeforeRedirect(string $redirectedToUrl): void
	{
	}

	protected function onAfterConnected(\CurlHandle $handle): void
	{
	}
}
