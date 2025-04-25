<?php

namespace Bitrix\TransformerController\Daemon\Http\Request;

use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

final class Convert extends Request
{
	public function __construct(
		private readonly string $convertUrl,
		private readonly string $filePath,
		private readonly array $formats,
		private readonly int $timeout,
	)
	{
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	public function send(): Result
	{
		$endpoint = $this->convertUrl;

		try
		{
			$rawResponse = $this->factory->getClient()->post(
				$endpoint,
				[
					'json' => [
						'src' => $this->filePath,
						'formats' => array_values($this->formats),
						'timeout' => $this->timeout,
					],
					// request can be quite long if the file is big
					'streamTimeout' => $this->timeout * count($this->formats) + 10,
					// we assume that convertUrl is hosted on localhost
					'privateIp' => true,
				],
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			$this->logger->error(
				'Error while sending file to http converter: {exceptionMessage}',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'convertUrl' => $endpoint,
				]
			);

			return (new Result())->addError(
				new Error\NotCritical(
					'Failed sending file to http converter',
					Error\Dictionary::TRANSFORMATION_FAILED,
				)
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
				'Wrong answer from convert url {convertUrl}',
				[
					'convertUrl' => $endpoint,
					'filePath' => $this->filePath,
					'formats' => $this->formats,
					'response' => $cuttedContent,
					'statusCode' => $rawResponse->getStatusCode(),
					'timeout' => $this->timeout,
				],
			);

			return (new Result())->addError(
				new Error\NotCritical(
					"Wrong answer from convert url {$endpoint} : {$cuttedContent}",
					Error\Dictionary::TRANSFORMATION_FAILED
				)
			);
		}

		if (!empty($decodedJson['data']['files']) && is_array($decodedJson['data']['files']))
		{
			$receivedFiles = $decodedJson['data']['files'];
		}
		else
		{
			$receivedFiles = [];
		}

		$result = new Result();
		$resultFiles = [];
		foreach ($receivedFiles as $format => $file)
		{
			DeleteQueue::getInstance()->add((string)$file);

			if (!in_array($format, $this->formats))
			{
				$this->logger->notice(
					'Unknown format received from convert url',
					[
						'convertUrl' => $endpoint,
						'filePath' => $this->filePath,
						'formats' => $this->formats,
						'responseDecoded' => $decodedJson,
						'format' => $format,
						'timeout' => $this->timeout,
					]
				);

				continue;
			}

			if (!file_exists($file))
			{
				$this->logger->error(
					'Convert url gave us file path in response, but this file doesnt exist',
					[
						'convertUrl' => $endpoint,
						'filePath' => $this->filePath,
						'formats' => $this->formats,
						'responseDecoded' => $decodedJson,
						'format' => $format,
						'resultFilePath' => $file,
						'timeout' => $this->timeout,
					]
				);

				$result->addError(
					new Error\NotCritical("Transformation to {$format} failed", Error\Dictionary::TRANSFORMATION_FAILED)
				);

				continue;
			}

			$resultFiles[$format] = $file;
		}

		$result->setDataKey('files', $resultFiles);

		if (!empty($decodedJson['errors']) || ($decodedJson['success'] ?? false) === false)
		{
			$this->logger->error(
				'Convert url sent us errors while we were trying to convert',
				[
					'convertUrl' => $endpoint,
					'filePath' => $this->filePath,
					'formats' => $this->formats,
					'responseDecoded' => $decodedJson,
					'timeout' => $this->timeout,
				],
			);

			foreach ($decodedJson['errors'] as $error)
			{
				if (isset($error['message']) || isset($error['code']))
				{
					$code = (string)($error['code'] ?? '');
					$message = (string)($error['message'] ?? '');

					$result->addError($this->createTransformerErrorByHttpConverterError($message, $code));
				}
			}
		}

		return $result;
	}

	private function createTransformerErrorByHttpConverterError(string $message, string $code): Error
	{
		$ourErrorCode = match ($code)
		{
			'FORMAT_TIMEOUT' => Error\Dictionary::TRANSFORMATION_TIMED_OUT,
			default => Error\Dictionary::TRANSFORMATION_FAILED,
		};

		return new Error\NotCritical($message, $ourErrorCode, [
			'originalErrorCode' => $code,
		]);
	}
}
