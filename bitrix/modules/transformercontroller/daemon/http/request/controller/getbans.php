<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\Controller;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Dto\Ban;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

final class GetBans extends Request
{
	/**
	 * @inheritDoc
	 */
	public function send(): Result
	{
		$endpoint = $this->prepareEndpoint();

		try
		{
			$response = $this->factory->getClient()->get(
				$endpoint,
				[
					'headers' => [
						'X-Bitrix-Daemon-Token' => Resolver::getCurrent()->controllerToken,
					],
					// we assume that the master controller is hosted on localhost
					'privateIp' => true,
				],
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			$this->logger->critical(
				'Error getting ban list from controller',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'banListUrl' => $endpoint,
				]
			);

			return (new Result())->addError(
				new Error('Could not get ban list from controller'),
			);
		}

		$content = Utils::getBodyString($response);
		try
		{
			$decodedJson = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			$cuttedResponse = Utils::cutResponse($content);

			$this->logger->critical(
				'Wrong answer from ban list url {banListUrl} while trying to get ban list',
				[
					'banListUrl' => $endpoint,
					'response' => $cuttedResponse,
				],
			);

			return (new Result())->addError(
				new Error('Wrong answer from ban list url'),
			);
		}

		if (!empty($decodedJson['error']) || ($decodedJson['success'] ?? false) === false)
		{
			if (empty($decodedJson['error']))
			{
				$decodedJson['error'] = ['Unknown error from ban list url'];
			}

			if (!is_array($decodedJson['error']))
			{
				$decodedJson['error'] = [$decodedJson['error']];
			}

			$this->logger->critical(
				'Controller sent us errors while we were getting ban list',
				[
					'banListUrl' => $endpoint,
					'responseDecoded' => $decodedJson,
				],
			);

			return (new Result())->addError(
				new Error(
					"Error getting ban list from {$endpoint} :" . PHP_EOL . implode("\t\n", $decodedJson['error']),
				)
			);
		}

		return (new Result())->setDataKey('bans', $this->prepareBans($decodedJson));
	}

	private function prepareEndpoint(): string
	{
		$config = Resolver::getCurrent();

		return (string)$this->factory->createUri($config->controllerBaseUrl)
			->withPath('/bitrix/tools/transformercontroller/ban.php')
			->withQuery(http_build_query(['action' => 'getListForWorker'], '', '&', PHP_QUERY_RFC3986))
		;
	}

	private function prepareBans(array $decodedJson): array
	{
		$bans = [];

		foreach (($decodedJson['data'] ?? []) as $banInfo)
		{
			if (!isset($banInfo['domain'], $banInfo['isPermanent']))
			{
				$this->logger->error(
					'Ban info from controller has unexpected structure, skipping it',
					[
						'banInfo' => $banInfo,
					]
				);

				continue;
			}

			$ban = new Ban();
			$ban->domain = $banInfo['domain'];
			$ban->queueName = $banInfo['queueName'] ?? null;
			$ban->isPermanent = $banInfo['isPermanent'];
			$ban->dateEndTimestamp = $banInfo['dateEndTimestamp'] ?? null;

			$bans[] = $ban;
		}

		return $bans;
	}
}
