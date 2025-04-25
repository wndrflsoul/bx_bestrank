<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;
use Bitrix\TransformerController\Runner\Runner;

class Document extends BaseCommand
{
	public const DIRECTORY = 'documents';
	public const DEFAULT_PATH = 'libreoffice';
	public const DEFAULT_CONVERT_PATH = 'convert';

	protected $file;
	protected $convert = '#LIBREOFFICE_PATH# #ENVDIR# --convert-to #FORMAT# --outdir #WORK_DIR# #FILE# --headless --display :0';
	protected $transformTypes = [
		'basic' => ['pdf', 'txt', 'csv'],
		'files' => ['jpg', 'pngAllPages'],
	];
	protected $fileUploader;

	/**
	 * Return array to validate parameters of this class.
	 *
	 * @return array
	 */
	protected static function getRequiredParams()
	{
		$requiredParams = parent::getRequiredParams();
		return array_merge($requiredParams,
			array(
				'file',
				'formats' => array('pdf', 'jpg', 'txt', 'text', 'md5', 'sha1', 'crc32', 'pngAllPages')
			)
		);
	}

	/**
	 * Document constructor.
	 * @param array $params Parameters of this document.
	 * @param Runner $runner Object to execute commands.
	 * @param FileUploader $fileUploader Object to work with files.
	 */
	public function __construct($params, Runner $runner, FileUploader $fileUploader)
	{
		parent::__construct($params, $runner);
		$this->file = (string)($params['file'] ?? '');
		$this->fileUploader = $fileUploader;
	}

	/**
	 * Parse parameters and perform particular operations on the file.
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function execute()
	{
		$downloadStartTime = time();
		$downloadResult = $this->fileUploader->downloadFile(self::DIRECTORY, $this->file);
		$downloadTime = time() - $downloadStartTime;

		$this->addResult('downloadTime', $downloadTime);

		if($downloadResult->isSuccess())
		{
			$downloadData = $downloadResult->getData();
			$this->file = $downloadData['file'];
			$this->fileUploader->setFiles($this->file);
			if(!$this->result->isSuccess())
			{
				$this->fileUploader->deleteFiles();
				return $this->result;
			}
			$fileResult = array();
			$formats = $this->params['formats'];
			// first basic transform
			foreach($formats as $key => $format)
			{
				if(!in_array($format, $this->transformTypes['basic']))
				{
					continue;
				}

				$fileResult[$format] = $this->transform($format);
				unset($formats[$key]);
			}
			$this->addResult('files', $fileResult);

			foreach($formats as $key => $format)
			{
				if(!in_array($format, $this->transformTypes['files']))
				{
					continue;
				}

				if(method_exists($this, $format))
				{
					$fileResult[$format] = $this->$format();
				}
				unset($formats[$key]);
			}
			$this->addResult('files', $fileResult);
			foreach($formats as $format)
			{
				if(method_exists($this, $format))
				{
					$methodResult = $this->$format($this->file);
					$this->addResult($format, $methodResult);
				}
			}
		}
		else
		{
			$this->result->addErrors($downloadResult->getErrors());
		}
		if(!$this->checkFinalResults())
		{
			$directory = $this->getDirectoryForSavingFilesOnError();
			if($directory)
			{
				$path = Path::combine($directory, randString());
				Log::logger()->debug(
					'save document on error to {path}',
					[
						'type' => 'document_transform',
						'path' => $path,
						'pid' => getmypid(),
					]
				);
				@copy($this->file, $path);
			}
		}
		$this->fileUploader->deleteFiles();
		return $this->result;
	}

	/**
	 * Make basic transformation through libreoffice.
	 *
	 * @param string $format Method of this class to call, or extension of the file to call in libreoffice.
	 * @param string $inputFile Full path to the file to transform.
	 * @return bool
	 */
	protected function transform($format, $inputFile = '')
	{
		if (empty($inputFile))
		{
			$inputFile = $this->file;
		}

		$envDir = '-env:UserInstallation=file://' . static::getLibreOfficeConfigUserPath();
		$libreofficePath = static::getLibreOfficePath();

		$workDir = Path::getDirectory($inputFile);

		$command = str_replace(
			[
				'#LIBREOFFICE_PATH#',
				'#ENVDIR#',
				'#FORMAT#',
				'#FILE#',
				'#WORK_DIR#',
			],
			[
				escapeshellcmd($libreofficePath),
				escapeshellarg($envDir),
				escapeshellarg($format),
				escapeshellarg($inputFile),
				escapeshellarg($workDir),
			],
			$this->convert
		);
		$resultExec = $this->exec($command);
		if($resultExec !== false)
		{
			$fileCandidate = $this->findFilename($resultExec, $format);
			if($fileCandidate)
			{
				$absolutePath = Cron::tryInvokeWithRestoringConnection(function() use ($fileCandidate) {
					return Path::convertSiteRelativeToAbsolute($fileCandidate);
				});
				if (File::isFileExists($fileCandidate))
				{
					$this->fileUploader->addToDeleteFiles($this->getPossibleLockFileName($fileCandidate));
					return $fileCandidate;
				}
				if (File::isFileExists($absolutePath))
				{
					$this->fileUploader->addToDeleteFiles($this->getPossibleLockFileName($absolutePath));
					return $absolutePath;
				}

				Log::logger()->error(
					'file found but does not exist',
					[
						'type' => 'document_transform',
						'format' => $format,
						'command' => $command,
						'file' => $fileCandidate,
						'absolutePath' => $absolutePath,
						'resultExec' => $resultExec,
						'pid' => getmypid(),
					]
				);
			}
			else
			{
				// we do not addError here or all the results won't be send
				Log::logger()->error(
					'cant find {format} in result {resultExec}',
					[
						'type' => 'document_transform',
						'format' => $format,
						'command' => $command,
						'resultExec' => $resultExec,
						'pid' => getmypid(),
					]
				);
			}
		}
		else
		{
			// we do not addError here or all the results won't be send
			Log::logger()->error(
				'cant exec {command}',
				['type' => 'document_transform', 'command' => $command, 'pid' => getmypid()],
			);
		}
		return false;
	}

	/**
	 * Check whether document is a spreadsheet.
	 *
	 * @return bool
	 */
	protected function isSpreadsheet()
	{
		$mimeTypes = array(
			'application/octet-stream', // .xlsx
			'application/vnd.oasis.opendocument.spreadsheet', // .ods
			'application/vnd.ms-office', // .xls
			'application/vnd.oasis.opendocument.spreadsheet-template', // .ots
			'application/xml', // .uos and others
		);
		$fileInfo = $this->exec('file -b --mime-type ' . escapeshellarg($this->file));
		foreach($fileInfo as $output)
		{
			if(in_array(trim($output), $mimeTypes))
			{
				return true;
			}
		}
		return false;
	}

	protected function isPdf(): bool
	{
		$fileInfo = $this->exec('file -b --mime-type ' . escapeshellarg($this->file));
		foreach ($fileInfo as $output)
		{
			if (trim($output) === 'application/pdf')
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Find result filename in libreoffice output.
	 *
	 * @param array $data Result of exec function.
	 * @param string $format Extension we are looking for.
	 * @return bool
	 */
	protected function findFilename($data, $format)
	{
		foreach($data as $output)
		{
			if(preg_match('#(\/[ _a-zA-Z0-9\.\/]+'.$format.')#U', $output, $matches))
			{
				return $matches[0];
			}
		}

		return false;
	}

	private function getPdfFile(): ?string
	{
		if ($this->isPdf())
		{
			return $this->file;
		}

		$result = $this->result->getData();
		if (isset($result['files']['pdf']))
		{
			$pdfFile = $result['files']['pdf'];
		}
		else
		{
			$pdfFile = $this->transform('pdf');
			$this->fileUploader->addToDeleteFiles($pdfFile);
		}

		if (!is_string($pdfFile))
		{
			return null;
		}

		return $pdfFile;
	}

	/**
	 * Get preview image from the document.
	 * If we have pdf - get image from it.
	 * If file is a spreadsheet we should get pdf first.
	 * If file is not a spreadsheet we can get image right from it.
	 *
	 * @return bool|string
	 */
	protected function jpg()
	{
		// $pdfFile = $this->getPdfFile();
		// if (!$pdfFile)
		// {
		// 	return null;
		// }
		//
		// return $this->transform('jpg', $pdfFile);

		return $this->transform('jpg');
	}

	protected function pngAllPages(): ?string
	{
		$pdfFile = $this->getPdfFile();
		if (!$pdfFile)
		{
			Log::logger()->error(
				'could not acquire pdf',
				[
					'type' => 'document_transform',
					'format' => 'pngAllPages',
					'message' => 'could not acquire pdf',
					'pid' => getmypid(),
				],
			);

			return null;
		}

		$pngFileName = $pdfFile . '.png';

		$template = '#CONVERT_PATH# -density 150 #INPUT# -quality 90 #OUTPUT#';
		$command = str_replace(
			[
				'#CONVERT_PATH#',
				'#INPUT#',
				'#OUTPUT#',
			],
			[
				escapeshellcmd(static::getConvertPath()),
				escapeshellarg($pdfFile),
				escapeshellarg($pngFileName),
			],
			$template
		);
		$resultExec = $this->exec($command);
		if ($resultExec !== false)
		{
			$pngs = [];
			// there is only one page
			if (File::isFileExists($pngFileName))
			{
				$pngs = ['0.png' => $pngFileName];
			}
			else
			{
				$counter = 0;
				$pngFileName = $pdfFile . '-' . $counter . '.png';
				while (File::isFileExists($pngFileName))
				{
					$pngs[$counter . '.png'] = $pngFileName;
					$counter++;
					$pngFileName = $pdfFile . '-' . $counter . '.png';
				}
			}
			$zipPath = $pdfFile . '_pngs.zip';
			$zipResult = $this->zipFiles($zipPath, $pngs);
			if ($zipResult->isSuccess())
			{
				return $zipPath;
			}

			Log::logger()->error(
				'Could not zip files',
				[
					'type' => 'document_transform',
					'format' => 'pngAllPages',
					'message' => 'Could not zip files',
					'errors' => implode(', ', $zipResult->getErrorMessages()),
					'pid' => getmypid(),
				],
			);
		}
		else
		{
			Log::logger()->error(
				'Error during command execution',
				[
					'type' => 'document_transform',
					'format' => 'pngAllPages',
					'message' => 'Error during command execution',
					'command' => $command,
					'pid' => getmypid(),
				],
			);
		}

		return null;
	}

	/**
	 * Get raw text from the document.
	 *
	 * @return bool|string
	 */
	protected function text()
	{
		$deleteAfter = false;
		$text = null;
		$result = $this->result->getData();
		if(!empty($result['files']['txt']))
		{
			$txtFile = $result['files']['txt'];
		}
		else
		{
			$txtFile = $this->transform('txt');
			$deleteAfter = true;
		}
		if($txtFile)
		{
			$text = File::getFileContents($txtFile);
			if($deleteAfter)
			{
				File::deleteFile($txtFile);
			}
		}
		return $text;
	}

	/**
	 * Returns path to libreoffice file
	 *
	 * @return string
	 */
	public static function getLibreOfficePath()
	{
		if(defined('BX_TC_SOFFICE_PATH'))
		{
			return BX_TC_SOFFICE_PATH;
		}

		return Option::get('transformercontroller', 'libreoffice_path', static::DEFAULT_PATH);
	}

	public static function getConvertPath(): string
	{
		if (defined('BX_TC_CONVERT_PATH'))
		{
			return (string)BX_TC_CONVERT_PATH;
		}

		return Option::get('transformercontroller', 'convert_path', static::DEFAULT_CONVERT_PATH);
	}

	public static function getLibreOfficeConfigUserPath($pid = null)
	{
		if(!$pid)
		{
			$pid = getmypid();
		}

		$path = sys_get_temp_dir();

		if(defined('BX_TC_SOFFICE_CONFIG_USER_PATH'))
		{
			$path = BX_TC_SOFFICE_CONFIG_USER_PATH;
		}

		return Path::combine($path, 'libreoffice-'.$pid);
	}

	protected function getPossibleLockFileName($filePath)
	{
		if(!empty($filePath))
		{
			$file = new File($filePath);
			$fileName = $file->getName();
			return $file->getDirectoryName().DIRECTORY_SEPARATOR.'.~lock.'.$fileName.'#';
		}

		return false;
	}

	/**
	 * @return false|string
	 */
	protected function getDirectoryForSavingFilesOnError()
	{
		if(defined('BX_TC_DIRECTORY_FOR_SAVING_DOCUMENTS_ON_ERROR'))
		{
			$directory = BX_TC_DIRECTORY_FOR_SAVING_DOCUMENTS_ON_ERROR;
			if(is_string($directory) && !empty($directory) && Directory::isDirectoryExists($directory))
			{
				return $directory;
			}
		}

		return false;
	}
}
