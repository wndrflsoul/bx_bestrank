<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\File\Upload;

use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Response;
use Bitrix\TransformerController\Daemon\Result;

class UploadFile extends Request
{
	public function __construct(
		private readonly Response\File\Upload\GetInfo $uploadInfo,
		private readonly string $filePath,
		private readonly int $fileSize,
		private readonly string $backUrl,
	)
	{
		parent::__construct();
	}

	public function send(): Result
	{
		$fileResource = fopen($this->filePath, 'rb');
		if (!$fileResource)
		{
			$this->logger->critical(
				'Could not open result file while trying to upload it to client',
				[
					'backUrl' => $this->backUrl,
					'filePath' => $this->filePath,
					'fileSize' => $this->fileSize,
				]
			);

			return (new Result())->addError(
				new Error(
					'Could not upload file because of internal server error. Check server logs or consult with support',
					Error\Dictionary::UPLOAD_FILES,
				),
			);
		}

		$chunkSize = $this->uploadInfo->getChunkSize();
		$numberOfChunks = (int)ceil($this->fileSize / $chunkSize);

		$this->logger->debug(
			'Starting uploading file {filePath} with total size {fileSize}.'
			. ' We will make {numberOfChunks} requests with {chunkSize} bytes chunks',
			[
				'backUrl' => $this->backUrl,
				'filePath' => $this->filePath,
				'fileSize' => $this->fileSize,
				'numberOfChunks' => $numberOfChunks,
				'chunkSize' => $chunkSize,
			]
		);

		for ($i = 0; $i < $numberOfChunks; $i++)
		{
			$chunk = fread($fileResource, $chunkSize);
			$isLastPart = $i + 1 === $numberOfChunks;

			$result = (new UploadFileChunk($this->backUrl, $this->uploadInfo, $this->fileSize, $isLastPart, $chunk))
				->setLoggerFluently($this->logger)
				->send()
			;
			if (!$result->isSuccess())
			{
				fclose($fileResource);

				return $result;
			}
		}

		fclose($fileResource);

		$this->logger->info(
			'File {filePath} uploaded to {backUrl}',
			[
				'backUrl' => $this->backUrl,
				'filePath' => $this->filePath,
				'fileSize' => $this->fileSize,
			]
		);

		return new Result();
	}
}
