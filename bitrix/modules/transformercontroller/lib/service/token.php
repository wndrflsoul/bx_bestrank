<?php

namespace Bitrix\TransformerController\Service;

use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Security\Sign\Signer;

/**
 * @internal
 */
final class Token
{
	private const SALT = 'transformercontroller.daemon';

	private Signer $signer;

	public function __construct()
	{
		$this->signer = new Signer();
	}

	public function generate(?string $guid = null): Result
	{
		$notEmptyGuid = $guid ?: Random::getString(16);

		return (new Result())->setData([
			'token' => $this->signer->sign($notEmptyGuid, self::SALT),
			'guid' => $notEmptyGuid,
		]);
	}

	public function check(HttpRequest $request): Result
	{
		$result = new Result();

		$token = $request->getHeader('X-Bitrix-Daemon-Token');
		if (empty($token))
		{
			return $result->addError(new Error('No token found'));
		}

		try
		{
			$slug = $this->signer->unsign($token, self::SALT);
		}
		catch (\Bitrix\Main\Security\Sign\BadSignatureException)
		{
			return $result->addError(new Error('Bad signature'));
		}

		if (empty($slug) || !is_string($slug))
		{
			return $result->addError(new Error('Bad guid'));
		}

		return $result->setData([
			'guid' => $slug,
		]);
	}
}
