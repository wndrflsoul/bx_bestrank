<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\IO\File;
use Bitrix\TransformerController\Runner\Runner;

class Video extends BaseCommand
{
	const DIRECTORY = 'video';

	const MAX_WIDTH = 1280;
	const MAX_HEIGHT = 720;

	private $file;
	private $fileUploader;

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
				'formats' => array('mp4', 'sha1', 'crc32', 'md5', 'jpg')
			)
		);
	}

	/**
	 * Video constructor.
	 * @param array $params Parameters of this document.
	 * @param Runner $runner Object to execute commands.
	 * @param FileUploader $fileUploader Object to work with files.
	 */
	public function __construct($params, Runner $runner, FileUploader $fileUploader)
	{
		parent::__construct($params, $runner);
		$this->file = $params['file'] ?? null;
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
			$this->file = $downloadData['file'] ?? null;
			$this->fileUploader->setFiles($this->file);
			if(!$this->result->isSuccess())
			{
				$this->fileUploader->deleteFiles();
				return $this->result;
			}
			$fileResult = array();
			foreach($this->params['formats'] as $key => $format)
			{
				$fileResult[$format] = $this->transform($format);
			}
			$this->addResult('files', $fileResult);
			$this->fileUploader->deleteFiles();
		}
		else
		{
			$this->result->addErrors($downloadResult->getErrors());
		}
		$this->checkFinalResults();
		return $this->result;
	}

	private function getCommandTemplateByFormat($format)
	{
		static $commandTemplates = null;
		if($commandTemplates === null)
		{
			$pathToErrorLog = Log::getPath('ffmpeg_error');
			$commandTemplates = array(
				'mp4' => 'ffmpeg -loglevel warning -i #FILE# -c:v libx264 -r 25 -vf scale=w="min(min('.self::MAX_WIDTH.'\,trunc('.self::MAX_WIDTH.'/max(a/1.7778\,1.7778/a)/2)*2)\,trunc(iw/2)*2):h=-2" -strict -2 -preset fast -pix_fmt yuv420p -codec:a aac -f mp4 #RESULT# 2> ' . escapeshellarg($pathToErrorLog),
				'jpg' => 'ffmpeg -loglevel warning -i #FILE# -an -ss 00:00:00 -vf scale=w="min(min('.self::MAX_WIDTH.'\,trunc('.self::MAX_WIDTH.'/max(a/1.7778\,1.7778/a)/2)*2)\,trunc(iw/2)*2):h=-2" -vframes: 1 -r 1 -y #RESULT#'
			);
		}

		if(isset($commandTemplates[$format]))
		{
			return $commandTemplates[$format];
		}

		return null;
	}

	/**
	 * Make new file with format $format from source video.
	 *
	 * @param string $format Extension of the file to call in ffmpeg.
	 * @return bool
	 */
	private function transform($format)
	{
		$commandTemplate = $this->getCommandTemplateByFormat($format);

		$resultFile = $this->file.'.'.$format;
		$command = str_replace(
			['#FILE#', '#RESULT#'],
			[escapeshellarg($this->file), escapeshellarg($resultFile)],
			$commandTemplate,
		);
		$resultExec = $this->exec($command);
		if($resultExec !== false)
		{
			if(File::isFileExists($resultFile))
			{
				return $resultFile;
			}
			else
			{
				Log::logger()->error(
					'cant find {format} after {command}',
					[
						'type' => 'video_transform',
						'format' => $format,
						'command' => $command,
						'pid' => getmypid(),
					],
				);
			}
		}
		else
		{
			Log::logger()->error(
				'cant exec {command}',
				['type' => 'video_transform', 'command' => $command, 'pid' => getmypid()],
			);
		}
		return false;
	}

	/**
	 * Returns max file size for this command.
	 *
	 * @param string $tarif
	 * @return int
	 */
	public static function getMaxFileSize($tarif = '')
	{
		if($tarif == 'B24_PROJECT')
		{
			return 314572800;
		}

		return 3221225472;
	}
}
