<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;

class Signal
{
	const CODE_DIE = '10';

	protected $pid = 0;

	public function __construct($pid)
	{
		$pid = intval($pid);

		if($pid <= 0)
		{
			Throw new ArgumentTypeException('pid', 'int');
		}

		$this->pid = $pid;
	}

	/**
	 * Get path to the file to store signal.
	 *
	 * @return string
	 */
	protected function getPath()
	{
		$uploadDirectory = FileUploader::provideLocalUploadPath();

		return Path::combine($_SERVER['DOCUMENT_ROOT'], $uploadDirectory, 'signals', $this->pid.'.msg');
	}

	/**
	 * Save signal into the file.
	 *
	 * @param string $code
	 * @return bool
	 */
	public function add($code)
	{
		if(empty($code))
		{
			return false;
		}

		if($this->sendByPosix($code))
		{
			return true;
		}

		return $this->sendByFile($code);
	}

	/**
	 * Returns signal. If none returns empty string.
	 *
	 * @return string
	 */
	public function get()
	{
		$code = '';
		$file = new File($this->getPath());
		if($file->isExists())
		{
			$code = $file->getContents();
			$file->delete();
		}

		return $code;
	}

	protected function sendByFile($code)
	{
		$code = (string)$code;
		if(empty($code))
		{
			return false;
		}
		return Settings::putFileContent($this->getPath(), $code);
	}

	protected function sendByPosix($code)
	{
		$code = (int)$code;
		return Cron::killProcessByPid($this->pid, $code);
		/*if(function_exists('posix_kill'))
		{
			return posix_kill($this->pid, $code);
		}*/
	}
}
