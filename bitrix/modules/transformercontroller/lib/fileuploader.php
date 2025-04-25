<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\HttpDebug;
use Bitrix\Main\Web\Json;

class FileUploader
{
	const MAX_CHUNK_SIZE = 10485760;
	/* Back url to upload files */
	protected $url = '';
	protected $files = [];
	protected $maxDownloadSize = 0;
	protected $fileSize = 0;
	protected $filesToDelete = [];

	/**
	 * @param string $url Back url.
	 * @return void
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @param array $files List of files to upload.
	 * @return void
	 */
	public function setFiles($files)
	{
		if(!empty($files))
		{
			if(!is_array($files))
			{
				$files = [$files];
			}
			$this->files = $files;
			$this->addToDeleteFiles($files);
		}
	}

	/**
	 * Download file from $url to local path.
	 *
	 * @param string $directory Directory name to save file.
	 * @param string $url Public url to download file.
	 * @return Result
	 */
	public function downloadFile($directory, $url)
	{
		$result = new Result();
		$downloadUrl = $url;
		$encodedDownloadUrl = \CHTTP::urnEncode($downloadUrl);
		$httpClient = new HttpClient($this->getDefaultTimeouts());
		Log::configureLogging($httpClient);
		$headers = $httpClient->head($encodedDownloadUrl);
		if($httpClient->getStatus() != 200)
		{
			$httpClient = new HttpClient($this->getDefaultTimeouts());
			Log::configureLogging($httpClient);
			$encodedDownloadUrl = \CHTTP::urnEncode(urldecode($downloadUrl));
			$headers = $httpClient->head($encodedDownloadUrl);
		}
		if($httpClient->getStatus() != 200)
		{
			return $result->addError(
				new Error(
					'Wrong http-status '.$httpClient->getStatus().' before download from '.$downloadUrl,
					TimeStatistic::ERROR_CODE_WRONG_STATUS_BEFORE_DOWNLOAD,
					[
						'httpStatus' => $httpClient->getStatus(),
						'url' => $downloadUrl,
					],
				)
			);
		}
		$downloadUrl = $encodedDownloadUrl;
		$contentType = $headers->get('content-type');
		if (strripos($contentType, 'text/html') !== false)
		{
			return $result->addError(
				new Error(
					'Wrong content-type text/html before download from '.$downloadUrl,
					TimeStatistic::ERROR_CODE_WRONG_CONTENT_TYPE_BEFORE_DOWNLOAD,
					[
						'contentType' => $contentType,
						'url' => $downloadUrl,
					],
				)
			);
		}
		$this->fileSize = intval($headers->get('content-length'));
		// in case there is no content-length in the response
		if($this->fileSize === 0)
		{
			$this->fileSize = $this->maxDownloadSize;
		}
		if($this->maxDownloadSize > 0 && $this->fileSize > $this->maxDownloadSize)
		{
			return $result->addError(
				new Error(
					'Download from '.$downloadUrl.' has been canceled: file is too big',
					TimeStatistic::ERROR_CODE_FILE_IS_TOO_BIG_ON_DOWNLOAD,
					[
						'maxDownloadSize' => $this->maxDownloadSize,
						'fileSize' => $this->fileSize,
						'url' => $downloadUrl,
					],
				)
			);
		}

		$isSendRangeHeader = (($headers->get('accept-ranges') == 'bytes') && $this->maxDownloadSize > 0);
		$newFile = Path::combine(self::provideLocalUploadPath(), $directory, Random::getString(10));

		[$httpClient, $isSuccess] = Http::sendWithRetry(
			'downloadFile',
			function () use ($isSendRangeHeader, $downloadUrl, $newFile): array {
				$httpClient = new HttpClient($this->getDefaultTimeouts(['bodyLengthMax' => $this->maxDownloadSize]));
				Log::configureLogging($httpClient);
				$httpClient->setDebugLevel($httpClient->getDebugLevel() & ~HttpDebug::RESPONSE_BODY);
				if($isSendRangeHeader)
				{
					$httpClient->setHeader('Range', 'bytes=0-'.$this->fileSize);
				}

				$isSuccess = $httpClient->download($downloadUrl, $newFile);

				return [$httpClient, $isSuccess];
			},
			function (array $result): bool {
				[$httpClient, ] = $result;

				$errors = $httpClient->getError();

				$isFileWasTooBigError = false;
				if (!empty($errors['NETWORK']) && is_string($errors['NETWORK']))
				{
					$isFileWasTooBigError = str_contains($errors['NETWORK'], 'Maximum content length has been reached');
				}

				return !$isFileWasTooBigError;
			}
		);

		if($isSuccess)
		{
			if($httpClient->getStatus() != 200 && $httpClient->getStatus() != 206)
			{
				File::deleteFile($newFile);
				$result->addError(
					new Error(
						'Wrong http-status '.$httpClient->getStatus().' after download from '.$downloadUrl,
						TimeStatistic::ERROR_CODE_WRONG_STATUS_AFTER_DOWNLOAD,
						[
							'httpStatus' => $httpClient->getStatus(),
							'url' => $downloadUrl,
						],
					)
				);
			}
			else
			{
				$sizeOfRealDownloadedFile = (new File($newFile))->getSize();
				if($sizeOfRealDownloadedFile > $this->maxDownloadSize)
				{
					return $result->addError(
						new Error(
							'file is too big',
							TimeStatistic::ERROR_CODE_FILE_IS_TOO_BIG_AFTER_DOWNLOAD,
							[
								'maxDownloadSize' => $this->maxDownloadSize,
								'fileSize' => $sizeOfRealDownloadedFile,
							],
						)
					);
				}
				$result->setData(['file' => $newFile]);
				$this->filesToDelete[] = $newFile;
			}
		}
		else
		{
			File::deleteFile($newFile);
			$result->addError(
				new Error(
					'Cant download file from '.$downloadUrl.': '.implode(', ', $httpClient->getError()),
					TimeStatistic::ERROR_CODE_CANT_DOWNLOAD_FILE,
					[
						'url' => $downloadUrl,
						'httpClientErrors' => $httpClient->getError(),
					],
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $timeouts
	 * @return array
	 */
	protected function getDefaultTimeouts(array $timeouts = []): array
	{
		$socketTimeout = 8;
		$streamTimeout = 30;
		if(defined('TRANSFORMER_CONTROLLER_DOWNLOAD_SOCKET_TIMEOUT') && is_int(TRANSFORMER_CONTROLLER_DOWNLOAD_SOCKET_TIMEOUT))
		{
			$socketTimeout = TRANSFORMER_CONTROLLER_DOWNLOAD_SOCKET_TIMEOUT;
		}
		if(defined('TRANSFORMER_CONTROLLER_DOWNLOAD_STREAM_TIMEOUT') && is_int(TRANSFORMER_CONTROLLER_DOWNLOAD_STREAM_TIMEOUT))
		{
			$streamTimeout = TRANSFORMER_CONTROLLER_DOWNLOAD_STREAM_TIMEOUT;
		}

		return array_merge(['socketTimeout' => $socketTimeout, 'streamTimeout' => $streamTimeout], $timeouts);
	}

	/**
	 * Returns path to upload directory
	 *
	 * @return string
	 */
	public static function getLocalUploadPath()
	{
		$uploadDirectory = \Bitrix\Main\Config\Option::get("main", "upload_dir", "upload");

		return Path::combine($_SERVER['DOCUMENT_ROOT'], $uploadDirectory, 'transformercontroller');
	}

	/**
	 * Returns path to upload directory. If it doesn't exist - creates it. If creation was unsuccessful - throws an
	 * exception.
	 *
	 * Basically the same as self::getLocalUploadPath, but more strict.
	 *
	 * @return string
	 * @throws SystemException
	 */
	final public static function provideLocalUploadPath(): string
	{
		$uploadPath = self::getLocalUploadPath();
		if (!Directory::isDirectoryExists($uploadPath))
		{
			try
			{
				Directory::createDirectory($uploadPath);
			}
			catch (\Exception $error)
			{
				throw new SystemException(
					'Could not create upload directory for transformercontroller',
					0,
					__FILE__,
					__LINE__,
					$error,
				);
			}
		}

		return $uploadPath;
	}

	/**
	 * @param string $filePath Full path to the file.
	 * @param array $uploadParams Parameters describing the way to upload.
	 * bucket - Id of the cloud bucket.
	 * chunk_size - Number of bytes to slice file.
	 * name - Name of the file.
	 * @return Result
	 */
	protected function uploadFile($filePath, $uploadParams)
	{
		$result = new Result();
		$file = fopen($filePath, 'rb');
		if($file)
		{
			$chunkSize = (int)($uploadParams['chunk_size'] ?? 0);
			if($chunkSize <= 0)
			{
				return $result->addError(
					new Error(
						'wrong chunkSize',
						0,
						[
							'uploadParams' => $uploadParams,
						],
					)
				);
			}
			$bucket = (int)($uploadParams['bucket'] ?? 0);
			$name = $uploadParams['name'] ?? null;
			$isLastPart = "n";
			$dataLength = filesize($filePath);
			$partsNumber = ceil($dataLength / $chunkSize);
			for($i = 0; $i < $partsNumber; $i++)
			{
				if($i + 1 == $partsNumber)
				{
					$isLastPart = "y";
				}

				$http = new HttpClient($this->getDefaultTimeouts(['waitResponse' => true, 'streamTimeout' => 300]));
				Log::configureLogging($http);
				$http->setDebugLevel($http->getDebugLevel() & ~HttpDebug::REQUEST_BODY);
				$chunk = fread($file, $chunkSize);
				if(isset($uploadParams['upload_type']) && $uploadParams['upload_type'] == 'file')
				{
					$filePost = ['content' => $chunk, 'filename' => $name];
				}
				else
				{
					$filePost = $chunk;
				}
				$post = ['file' => $filePost, 'file_name' => $name, 'file_size' => $dataLength, 'last_part' => $isLastPart];

				if($bucket > 0)
				{
					$post['bucket'] = $bucket;
				}

				$resultJson = $http->post($this->url, $post, true);
				$cuttedResultJson = mb_substr((string)$resultJson, 0, Settings::getResponseMaxLengthInLogs());
				try
				{
					$answer = Json::decode($resultJson);
				}
				catch(ArgumentException $e)
				{
					return $result->addError(
						new Error(
							'wrong answer from back_url '.$this->url.': '.print_r($cuttedResultJson, 1),
							0,
							[
								'url' => $this->url,
								'response' => $cuttedResultJson,
							],
						)
					);
				}
				if(!empty($answer['error']))
				{
					if(!is_array($answer['error']))
					{
						$answer['error'] = [$answer['error']];
					}

					return $result->addError(
						new Error(
							'error uploading file '.$filePath.' to '.$this->url.': '.PHP_EOL.implode("\t\n", $answer['error']),
							0,
							[
								'file' => $filePath,
								'url' => $this->url,
								'response' => $cuttedResultJson,
								'responseDecoded' => $answer,
							],
						)
					);
				}
			}
			Log::logger()->info(
				'file {file} uploaded to {url}',
				['type' => 'file_uploader', 'url' => $this->url, 'file' => $filePath, 'pid' => getmypid()]
			);
		}
		else
		{
			$result->addError(
				new Error(
					'cant open file '.$filePath,
					0,
					[
						'file' => $filePath,
					],
				)
			);
		}

		return $result;
	}

	/**
	 * @return void
	 */
	public function deleteFiles()
	{
		foreach($this->filesToDelete as $file)
		{
			if($file)
			{
				File::deleteFile($file);
			}
		}
		$this->filesToDelete = [];
	}

	/**
	 * Add $files to array of files that will be deleted after command is complete.
	 *
	 * @param $files
	 */
	public function addToDeleteFiles($files)
	{
		if(!is_array($files))
		{
			$files = [$files];
		}
		$this->filesToDelete = array_merge($this->filesToDelete, $files);
	}

	/**
	 * Get chunk size, bucket id and filename from the client.
	 *
	 * @param string $file Path to file.
	 * @param string $key Extension of the file.
	 * @return Result
	 */
	protected function getUploadInfo($file, $key)
	{
		$result = new Result();
		if(!File::isFileExists($file))
		{
			return $result->addError(
				new Error(
					'error trying to read file '.$file.' before upload',
					0,
					[
						'file' => $file,
						'fileKey' => $key,
					],
				)
			);
		}
		$fileSize = filesize($file);
		$post = array('file_id' => $key, 'file_size' => $fileSize, 'upload' => 'where');

		[, $resultJson] = Http::sendWithRetry('getUploadInfo', function () use ($post): array {
			$http = new HttpClient($this->getDefaultTimeouts(['waitResponse' => true]));
			Log::configureLogging($http);
			$resultJson = $http->post($this->url, $post);

			return [$http, $resultJson];
		});
		$cuttedResultJson = mb_substr((string)$resultJson, 0, Settings::getResponseMaxLengthInLogs());

		try
		{
			$answer = Json::decode($resultJson);
		}
		catch(ArgumentException $e)
		{
			return $result->addError(
				new Error(
					'wrong answer from back_url '.$this->url.' : '.print_r($cuttedResultJson, 1),
					0,
					[
						'url' => $this->url,
						'response' => $cuttedResultJson
					],
				)
			);
		}
		if(!empty($answer['error']))
		{
			if(!is_array($answer['error']))
			{
				$answer['error'] = [$answer['error']];
			}

			return $result->addError(
				new Error(
					'error getting upload info file '.$file.' from '.$this->url.': '.PHP_EOL.implode("\t\n", $answer['error']),
					0,
					[
						'file' => $file,
						'fileKey' => $key,
						'url' => $this->url,
						'response' => $cuttedResultJson,
						'responseDecoded' => $answer,
					],
				),
			);
		}
		return $result->setData($answer);
	}

	/**
	 * @return Result
	 */
	public function uploadFiles()
	{
		$result = new Result();
		$files = array();
		foreach ($this->files as $key => $file)
		{
			if (empty($file))
			{
				continue;
			}

			$getUploadInfoResult = $this->getUploadInfo($file, $key);
			$uploadParams = $getUploadInfoResult->getData();
			if(!$getUploadInfoResult->isSuccess())
			{
				$result->addErrors($getUploadInfoResult->getErrors());
			}
			else
			{
				if(isset($uploadParams['chunk_size']) && $uploadParams['chunk_size'] > self::MAX_CHUNK_SIZE)
				{
					$uploadParams['chunk_size'] = self::MAX_CHUNK_SIZE;
				}
				$files[$key] = $uploadParams['name'] ?? null;
				$uploadResult = self::uploadFile($file, $uploadParams);
				if(!$uploadResult->isSuccess())
				{
					$result->addErrors($uploadResult->getErrors());
				}
			}
		}
		$result->setData($files);
		return $result;
	}

	/**
	 * Set maximim size of the file to download.
	 *
	 * @param int $maxDownloadSize
	 */
	public function setMaxDownloadSize($maxDownloadSize)
	{
		$this->maxDownloadSize = $maxDownloadSize;
	}

	/**
	 * Returns size of the input file.
	 *
	 * @return int
	 */
	public function getFileSize()
	{
		return $this->fileSize;
	}

	/**
	 * Check size of the file.
	 * Returns true of file size is lower then limit.
	 *
	 * @param $filePath
	 * @return bool
	 */
	protected function checkFileSize($filePath)
	{
		$file = new File($filePath);
		if($file->getSize() > $this->maxDownloadSize)
		{
			return false;
		}

		return true;
	}
}
