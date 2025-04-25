<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\File\Upload;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Response;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

final class UploadFileChunk extends Request
{
	public function __construct(
		private readonly string $backUrl,
		private readonly Response\File\Upload\GetInfo $uploadInfo,
		private readonly int $fileSize,
		private readonly bool $isLastPart,
		private readonly string $chunk,
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
					'multipart' => $this->prepareMultipart(),
					'streamTimeout' => Resolver::getCurrent()->uploadChunkStreamTimeout,
				],
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			$this->logger->error(
				'Error uploading file chunk to client',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'backUrl' => $this->backUrl,
					'fileSize' => $this->fileSize,
					'isLastPart' => $this->isLastPart,
				],
			);

			return (new Result())->addError(
				new Error('Failed to upload result file chunk', Error\Dictionary::UPLOAD_FILES),
			);
		}

		$content = Utils::getBodyString($rawResponse);

		try
		{
			$decodedJson = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			$cuttedResponse = Utils::cutResponse($content);

			$this->logger->error(
				'Wrong answer from back_url {backUrl} while trying to upload file chunk',
				[
					'backUrl' => $this->backUrl,
					'response' => $cuttedResponse,
					'fileSize' => $this->fileSize,
					'isLastPart' => $this->isLastPart,
				]
			);

			return (new Result())->addError(
				new Error(
					"Wrong answer from back_url {$this->backUrl}: {$cuttedResponse}",
					Error\Dictionary::UPLOAD_FILES,
				)
			);
		}

		if (!empty($decodedJson['error']))
		{
			if (!is_array($decodedJson['error']))
			{
				$decodedJson['error'] = [$decodedJson['error']];
			}

			$this->logger->error(
				'Client sent us errors while we were uploading file chunk',
				[
					'backUrl' => $this->backUrl,
					'responseDecoded' => $decodedJson,
					'fileSize' => $this->fileSize,
					'isLastPart' => $this->isLastPart,
				],
			);

			return (new Result())->addError(
				new Error(
					"Error uploading file to {$this->backUrl} :" . PHP_EOL . implode("\t\n", $decodedJson['error']),
					Error\Dictionary::UPLOAD_FILES,
				)
			);
		}

		return new Result();
	}

	private function prepareMultipart(): array
	{
		$formData = [
			'file_name' => $this->uploadInfo->getName(),
			'file_size' => $this->fileSize,
			'last_part' => $this->isLastPart ? 'y' : 'n',
			'file' => $this->uploadInfo->isSendChunkAsBinaryString() ? $this->chunk : [
				'filename' => $this->uploadInfo->getName(),
				'content' => $this->chunk,
			],
		];

		if ($this->uploadInfo->getBucket() > 0)
		{
			$formData['bucket'] = $this->uploadInfo->getBucket();
		}

		return $formData;
	}
}
