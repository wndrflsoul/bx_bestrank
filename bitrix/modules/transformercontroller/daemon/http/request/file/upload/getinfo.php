<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\File\Upload;

use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Response;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

class GetInfo extends Request
{
	public function __construct(
		private readonly string $backUrl,
		private readonly string $format,
		private readonly int $fileSize,
	)
	{
		parent::__construct();
	}

	public function send(): Result
	{
		$client = $this->factory->getClient();

		try
		{
			$rawResponse = $client->post(
				$this->backUrl,
				[
					'form' => [
						'file_id' => $this->format,
						'file_size' => $this->fileSize,
						'upload' => 'where',
					],
				],
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			$this->logger->error(
				'Error getting upload info from client',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'backUrl' => $this->backUrl,
					'format' => $this->format,
					'fileSize' => $this->fileSize,
				]
			);

			return (new Result())->addError(
				new Error("Error getting upload info for {$this->format} file", Error\Dictionary::UPLOAD_FILES)
			);
		}

		$content = Utils::getBodyString($rawResponse);

		try
		{
			$decodedJson = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			$cuttedContent = Utils::cutResponse($content);

			$this->logger->error(
				'Wrong answer from back_url {backUrl} while trying to get upload info',
				[
					'backUrl' => $this->backUrl,
					'format' => $this->format,
					'fileSize' => $this->fileSize,
					'response' => $cuttedContent,
				],
			);

			return (new Result())->addError(
				new Error(
					"Wrong answer from back_url {$this->backUrl} : {$cuttedContent}",
					Error\Dictionary::UPLOAD_FILES
				)
			);
		}

		if (!empty($decodedJson['error']))
		{
			if(!is_array($decodedJson['error']))
			{
				$decodedJson['error'] = [$decodedJson['error']];
			}

			$this->logger->error(
				'Client sent us errors while we were getting upload info',
				[
					'backUrl' => $this->backUrl,
					'format' => $this->format,
					'fileSize' => $this->fileSize,
					'responseDecoded' => $decodedJson,
				],
			);

			return (new Result())->addError(
				new Error(
					"Error getting upload info from {$this->backUrl}: "
					. PHP_EOL . implode("\t\n", $decodedJson['error']),
					Error\Dictionary::UPLOAD_FILES,
				),
			);
		}

		return (new Result())->setDataKey(
			'response',
			(new Response\File\Upload\GetInfo($decodedJson)),
		);
	}
}
