<?php

namespace Bitrix\TransformerController\Daemon\Http;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Psr\Http\Message\ResponseInterface;

final class Utils
{
	public static function cutResponse(string|ResponseInterface $response): string
	{
		$responseString = is_string($response) ? $response : self::getBodyString($response);

		return mb_substr($responseString, 0, Resolver::getCurrent()->responseMaxLengthInLogs);
	}

	public static function getBodyString(ResponseInterface $response): string
	{
		$body = $response->getBody();
		if ($body->isSeekable())
		{
			$body->rewind();
		}

		return $body->getContents();
	}
}
