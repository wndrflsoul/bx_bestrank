<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\Controller;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Dto\Statistic;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

final class AddStatistic extends Request
{
	public function __construct(
		private readonly string $guid,
		private readonly Statistic $stats
	)
	{
		parent::__construct();
	}

	public function send(): Result
	{
		$endpoint = $this->getEndpoint();

		try
		{
			$response = $this->factory->getClient()->post(
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
				'Error sending statistics about job to the controller',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'statisticsUrl' =>  $endpoint,
				],
			);

			return (new Result())->addError(
				new Error('Failed to send statistics about job to the controller')
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
				'Wrong answer from statistics url {statisticsUrl} while trying to send statistics about job',
				[
					'statisticsUrl' =>  $endpoint,
					'response' => $cuttedResponse,
				]
			);

			return (new Result())->addError(
				new Error("Wrong answer from statistics url {$endpoint} : {$cuttedResponse}")
			);
		}

		if (!empty($decodedJson['error']) || ($decodedJson['success'] ?? false) === false)
		{
			if (empty($decodedJson['error']))
			{
				$decodedJson['error'] = ['Unknown error from statistics url'];
			}

			if (!is_array($decodedJson['error']))
			{
				$decodedJson['error'] = [$decodedJson['error']];
			}

			$this->logger->critical(
				'Controller sent us errors while we were sending job statistics',
				[
					'statisticsUrl' => $endpoint,
					'responseDecoded' => $decodedJson,
				],
			);

			return (new Result())->addError(
				new Error(
					"Error sending statistics to {$endpoint} :" . PHP_EOL . implode("\t\n", $decodedJson['error']),
				)
			);
		}

		return new Result();
	}

	private function getEndpoint(): string
	{
		$queryParams = [
			'data' => 'add',
			'guid' => $this->guid,
			'fileSize' => $this->stats->fileSize,
			'error' => $this->stats->error?->getMessage(),
			'errorCode' => $this->stats->error?->getCode(),
			'startTimestamp' => $this->stats->startTimestamp,
			'timeDownload' => $this->stats->timeDownload,
			'timeExec' => $this->stats->timeExec,
			'timeUpload' => $this->stats->timeUpload,
			'endTimestamp' => $this->stats->endTimestamp,
		];

		return (string)$this->factory->createUri(Resolver::getCurrent()->controllerBaseUrl)
			->withPath('/bitrix/tools/transformercontroller/get_statistic.php')
			->withQuery(http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986))
		;
	}
}
