<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlException;

/**
 * This class is designed to work fine without bitrix core.
 */
class Cron
{
	const CRONTAB_PATH = '/bitrix/crontab/crontab.cfg';

	const WORKER_PATH = '/bitrix/modules/transformercontroller/tools/worker.php';
	const WORDERD_PATH = '/bitrix/modules/transformercontroller/tools/sys_workerd.php';

	/**
	 * Return number of currently running worker.php processes.
	 *
	 * @return int
	 */
	public static function getProcesses()
	{
		exec('ps aux | grep "sys_workerd.php"', $grepResult);
		return(count($grepResult) - 3);
	}

	/**
	 * Launch one more worker.php.
	 *
	 * @return bool;
	 */
	public static function startWorker()
	{
		return exec('php -f '.escapeshellarg($_SERVER['DOCUMENT_ROOT'].self::WORKER_PATH).' > /dev/null &');
	}

	/**
	 * Return list of current crontab commands.
	 *
	 * @return array
	 */
	protected static function getCurrentCrontab()
	{
		$cronList = [];
		exec('crontab -l', $currentCron);
		if(mb_strpos($currentCron[0], 'no crontab') === false)
		{
			return $currentCron;
		}
		return $cronList;
	}

	/**
	 * Update crontab with list of commands.
	 * Put them in file before save.
	 *
	 * @param array $cronList List of commands for crontab.
	 * @return bool|string
	 */
	protected static function updateCrontab($cronList)
	{
		if(empty($cronList))
		{
			return exec('crontab -r');
		}
		else
		{
			$cronContent = implode("\n", $cronList)."\n";
			if(Settings::putFileContent($_SERVER['DOCUMENT_ROOT'].self::CRONTAB_PATH, $cronContent))
			{
				return exec('crontab '.escapeshellarg($_SERVER['DOCUMENT_ROOT'].self::CRONTAB_PATH));
			}
			return false;
		}
	}

	/**
	 * Add to crontab workerd.php command.
	 *
	 * @return bool
	 */
	public static function addToCrontab()
	{
		$settings = Settings::getSettings();
		$cronTime = $settings['cron_time'];
		$processes = $settings['processes'];
		$cronList = self::getCurrentCrontab();
		foreach($cronList as $key => $command)
		{
			if(mb_strpos($command, self::WORDERD_PATH) !== false)
			{
				unset($cronList[$key]);
			}
		}
		$cronList[] = '*/'.$cronTime.' * * * * php -f '.$_SERVER['DOCUMENT_ROOT'].self::WORDERD_PATH.' '.$processes;
		return self::updateCrontab($cronList);
	}

	/**
	 * Delete from crontab workerd.php command.
	 *
	 * @return bool|string
	 */
	public static function deleteFromCrontab()
	{
		$cronList = self::getCurrentCrontab();
		foreach($cronList as $key => $command)
		{
			if(mb_strpos($command, self::WORDERD_PATH) !== false)
			{
				unset($cronList[$key]);
			}
		}
		return self::updateCrontab($cronList);
	}

	/**
	 * Check is there workerd.php in crontab.
	 *
	 * @return bool
	 */
	public static function getCrontabStatus()
	{
		$cronList = self::getCurrentCrontab();
		foreach($cronList as $key => $command)
		{
			if(mb_strpos($command, self::WORDERD_PATH) !== false)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns array of workers pids.
	 *
	 * @return array
	 */
	public static function getWorkerPids()
	{
		$pids = [];

		if(exec('ps ax | grep ' . escapeshellarg(self::WORKER_PATH), $grepResult))
		{
			$pidPattern = '#^(\d+)\s#';
			foreach($grepResult as $process)
			{
				$process = trim($process);
				if(preg_match($pidPattern, $process, $pidMatches) && mb_strpos($process, 'php -f') !== false)
				{
					$pids[] = $pidMatches[1];
				}
			}
		}

		return $pids;
	}

	/**
	 * Try to kill all worker.php processes.
	 */
	public static function killWorkers()
	{
		$pids = self::getWorkerPids();
		foreach($pids as $pid)
		{
			self::killWorker($pid);
		}
	}

	/**
	 * Kill one worker.php by pid
	 *
	 * @param $pid
	 */
	public static function killWorker($pid)
	{
		$pid = intval($pid);
		if($pid > 0)
		{
			$signal = new Signal($pid);
			if(!$signal->add(Signal::CODE_DIE))
			{
				self::killProcessByPid($pid);
			}
		}
	}

	/**
	 * Find all processes that should be terminated.
	 * @see self::getCommandsMaxTime()
	 *
	 */
	public static function killSlowProcesses()
	{
		foreach(self::getCommandsMaxTime() as $name => $maxTime)
		{
			if(empty($name))
			{
				continue;
			}
			$command = 'ps ax -o pid -o etimes -o command | grep ' . escapeshellarg($name);
			if(exec($command, $grepResult))
			{
				$pidPattern = '#^(\d+)\s#';
				$timePattern = '#\s(\d+)\s#';
				$filePattern = '#(\\'.$_SERVER['DOCUMENT_ROOT'].'[a-zA-Z0-9\/]+)#';
				foreach($grepResult as $result)
				{
					$sourceFile = false;
					$result = trim($result);
					if(preg_match($timePattern, $result, $timeMatches))
					{
						$time = $timeMatches[1];
						$fileSize = 0;
						if(preg_match($filePattern, $result, $pathMatches))
						{
							$fileSize = filesize($pathMatches[1]);
							$sourceFile = $pathMatches[1];
						}
						if(is_array($maxTime))
						{
							if($fileSize == 0)
							{
								$maxResultTime = max($maxTime);
							}
							else
							{
								$maxResultTime = min($maxTime);
								foreach($maxTime as $sizeLimit => $timeLimit)
								{
									$maxResultTime = $timeLimit;
									if($sizeLimit < $fileSize)
									{
										continue;
									}
									break;
								}
							}
						}
						else
						{
							$maxResultTime = $maxTime;
						}
						if($time > $maxResultTime)
						{
							if(preg_match($pidPattern, $result, $pidMatches))
							{
								$pid = $pidMatches[1];
								self::killProcessByPid($pid);
								$log = new Log(true);
								$log::logger()->info(
									'process killed: {result}',
									['type' => 'cron', 'killedPid' => $pid, 'result' => $result, 'pid' => getmypid()],
								);
								if($sourceFile)
								{
									$resultFile = $sourceFile.'.'.static::getResultFileExtensionByCommand($name);
									if(file_exists($resultFile))
									{
										unlink($resultFile);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Returns array where keys - name of the commands and values - maximum execution time in seconds.
	 *
	 * @return array
	 */
	public static function getCommandsMaxTime()
	{
		// todo load command names or even pids dynamically
		return [
			'libreoffice' => static::getMaxLibreOfficeExecutionTime(),
			'ffmpeg' => [
				1048576 => 180,
				10485760 => 300,
				104857600 => 600,
				304857600 => 900,
				1073741824 => 1800,
				3221225472 => 3600,
			],
		];
	}

	/**
	 * @return int
	 */
	protected static function getMaxLibreOfficeExecutionTime()
	{
		// it is not efficiently to set it less than 50 because of sleepCounter in sys_workerd.php
		if(defined('BX_TC_CRON_LIBREOFFICE_MAX_TIME'))
		{
			$maxTime = intval(BX_TC_CRON_LIBREOFFICE_MAX_TIME);
			if($maxTime > 0)
			{
				return $maxTime;
			}
		}

		return 60;
	}

	/**
	 * @param string $command
	 * @return string
	 */
	protected static function getResultFileExtensionByCommand($command)
	{
		$map = [
			'libreoffice' => 'pdf',
			'ffmpeg' => 'mp4',
		];

		return $map[$command];
	}

	/**
	 * Try to kill process with pid=$pid. Returns true on success, false otherwise.
	 *
	 * @param int $pid
	 * @param int $code
	 * @return bool
	 */
	public static function killProcessByPid($pid, $code = 9)
	{
		$killResult = false;
		exec('kill -'.(int)$code.' '.(int)$pid, $killResult);
		return $killResult;
	}

	/**
	 * Change current php script directory. All shell commands will be executed in $dir.
	 *
	 * @param string $dir
	 */
	public static function changeDirectory($dir)
	{
		chdir($dir);
	}

	public static function tryInvokeWithRestoringConnection(callable $function)
	{
		try
		{
			$result = $function();
		}
		catch (SqlException)
		{
			Application::getConnection()?->disconnect();

			global $DB;
			$DB->Connect($DB->DBHost, $DB->DBName, $DB->DBLogin, $DB->DBPassword);

			$result = $function();
		}

		return $result;
	}
}
