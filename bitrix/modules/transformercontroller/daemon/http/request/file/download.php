<?php

namespace Bitrix\TransformerController\Daemon\Http\Request\File;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;
use Bitrix\TransformerController\Daemon\File\Type;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

final class Download extends Request
{
	public function __construct(
		private readonly string $fileUrl,
		private readonly Type $fileType,
	)
	{
		parent::__construct();
	}

	public function send(): Result
	{
		$result = new Result();

		$downloadedFilePath = $this->getFileSavePath();
		DeleteQueue::getInstance()->add($downloadedFilePath);

		try
		{
			$this->factory->getClient()->download(
				$this->fileUrl,
				$downloadedFilePath,
				[
					'bodyLengthMax' => $this->fileType->getMaxFileSize(),
					'shouldFetchBody' => fn(ResponseInterface $response) => $this->shouldFetchBody($response, $result),
				],
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			// download was aborted in 'shouldFetchBody'
			if (!$result->isSuccess())
			{
				return $result;
			}

			// todo leaky abstraction?
			if ($exception->getCode() === CURLE_FILESIZE_EXCEEDED)
			{
				$this->addFileIsTooBigError($result);

				return $result;
			}

			$this->logger->error(
				'Could not download file from client: {exceptionMessage}',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'fileUrl' => $this->fileUrl,
				]
			);

			return (new Result())->addError(
				new Error("Cant download file from {$this->fileUrl}", Error\Dictionary::CANT_DOWNLOAD_FILE)
			);
		}

		// download was aborted in 'shouldFetchBody'
		if (!$result->isSuccess())
		{
			return $result;
		}

		if (!is_readable($downloadedFilePath))
		{
			$this->logger->error(
				'Successfully downloaded file from client, but local file copy is not readable',
				[
					'fileUrl' => $this->fileUrl,
					'filePath' => $downloadedFilePath,
				]
			);

			return (new Result())->addError(
				new Error(
					"Download failed from {$this->fileUrl} for unknown reason",
					Error\Dictionary::CANT_DOWNLOAD_FILE,
				)
			);
		}

		$realFileSize = filesize($downloadedFilePath);
		if ($realFileSize > $this->fileType->getMaxFileSize())
		{
			$this->logger->error(
				'Downloaded file from client and file was bigger than max allowed file size',
				[
					'fileSize' => $realFileSize,
					'maxDownloadSize' => $this->fileType->getMaxFileSize(),
					'fileUrl' => $this->fileUrl,
				]
			);

			return (new Result())->addError(
				new Error('File is too big', Error\Dictionary::FILE_IS_TOO_BIG_AFTER_DOWNLOAD)
			);
		}

		$this->logger->info(
			'Downloaded file {filePath} from {fileUrl}',
			[
				'filePath' => $downloadedFilePath,
				'fileSize' => $realFileSize,
				'fileUrl' => $this->fileUrl,
			]
		);

		return (new Result())
			->setDataKey('file', $downloadedFilePath)
			->setDataKey('fileSize', $realFileSize)
		;
	}

	private function getFileSavePath(): string
	{
		$tmpFilesDir = Resolver::getCurrent()->tmpFilesDir;

		if (is_file($tmpFilesDir))
		{
			$this->logger->critical(
				'Director for tmp files is a file, not directory. It seems that config is wrong, aborting',
				[
					'tmpFilesDir' => $tmpFilesDir,
				],
			);

			throw new \RuntimeException('Tmp directory is a file: ' . $tmpFilesDir);
		}

		if (!is_dir($tmpFilesDir))
		{
			@mkdir($tmpFilesDir, 0755);
		}

		if (!is_dir($tmpFilesDir) || !is_writable($tmpFilesDir))
		{
			$this->logger->critical(
				'Directory for tmp files dont exists or is not available for writing'
				. '. I\'ve tried to create it, but it seems I failed',
				[
					'tmpFilesDir' => $tmpFilesDir,
				]
			);

			throw new \RuntimeException('Tmp directory is not writable: ' . $tmpFilesDir);
		}

		$fileTypeSubdir = $tmpFilesDir . DIRECTORY_SEPARATOR . $this->fileType->getSlug()->value;
		if (!is_dir($fileTypeSubdir))
		{
			@mkdir($fileTypeSubdir, 0755);
		}

		if (!is_dir($fileTypeSubdir) || !is_writable($fileTypeSubdir))
		{
			$this->logger->critical(
				'Sub-directory for downloading files of tmp files directory dont exists or is not available for writing'
				. '. I\'ve tried to create it, but it seems I failed',
				[
					'tmpFilesDir' => $tmpFilesDir,
					'subDir' => $fileTypeSubdir,
				],
			);

			throw new \RuntimeException('Tmp sub-directory is not writable: ' . $fileTypeSubdir);
		}

		return $fileTypeSubdir . '/' . bin2hex(random_bytes(10));
	}

	private function shouldFetchBody(ResponseInterface $response, Result $result): bool
	{
		if ($response->getStatusCode() !== 200)
		{
			$message = "Wrong http-status {$response->getStatusCode()} before download from {$this->fileUrl}";

			$this->logger->error(
				$message,
				[
					'fileUrl' => $this->fileUrl,
					'httpStatus' => $response->getStatusCode(),
				]
			);

			$result->addError(
				new Error($message, Error\Dictionary::WRONG_STATUS_BEFORE_DOWNLOAD),
			);

			return false;
		}

		$contentType = current($response->getHeader('Content-Type'));
		if (str_contains((string)$contentType, 'text/html'))
		{
			$message = "Wrong content-type text/html before download from {$this->fileUrl}";

			$this->logger->error($message, ['contentType' => $contentType, 'fileUrl' => $this->fileUrl]);

			$result->addError(
				new Error($message, Error\Dictionary::WRONG_CONTENT_TYPE_BEFORE_DOWNLOAD),
			);

			return false;
		}

		$contentLength = current($response->getHeader('Content-Length')) ?: $this->fileType->getMaxFileSize();

		if ($contentLength > $this->fileType->getMaxFileSize())
		{
			$this->addFileIsTooBigError($result, (int)$contentLength);

			return false;
		}

		return true;
	}

	private function addFileIsTooBigError(Result $result, ?int $fileSize = null): void
	{
		$message = "Download from {$this->fileUrl} has been canceled: file is too big";

		$this->logger->error(
			$message,
			[
				'fileUrl' => $this->fileUrl,
				'fileSize' => $fileSize,
				'maxDownloadSize' => $this->fileType->getMaxFileSize(),
			]
		);

		$result->addError(
			new Error($message, Error\Dictionary::FILE_IS_TOO_BIG_ON_DOWNLOAD)
		);
	}
}
