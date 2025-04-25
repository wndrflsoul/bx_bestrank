<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;

class Settings
{
	const SETTINGS_PATH = '/bitrix/modules/transformercontroller_cron_settings.data';
	const DEFAULT_WORKERS_TTL = 1800;

	protected $lastTableAccess = 0;
	protected $data = [];
	protected $workersCache = [];
	protected $maximumProcesses = 0;

	public function __construct($maximumProcesses = 0)
	{
		$this->data = static::getSettings();
		if($maximumProcesses <= 0)
		{
			if(isset($this->data['processes']))
			{
				$maximumProcesses = intval($this->data['processes']);
			}
		}
		if($maximumProcesses > 0)
		{
			$this->maximumProcesses = $maximumProcesses;
		}
	}

	/**
	 * @return \CDatabase
	 */
	protected function getDatabase()
	{
		global $DB;
		return $DB;
	}

	/**
	 * Save array of the settings to the file.
	 *
	 * @param array $settings Settings to save in file.
	 * @return void
	 */
	public static function saveSettings($settings)
	{
		$data = serialize($settings);
		$filename = $_SERVER['DOCUMENT_ROOT'].self::SETTINGS_PATH;
		self::putFileContent($filename, $data);
	}

	/**
	 * Get array of the settings from the file.
	 * If file is empty, returns default module parameters.
	 * If they are empty as well returns internal default settings.
	 *
	 * @return array|bool
	 */
	public static function getSettings()
	{
		$filename = $_SERVER['DOCUMENT_ROOT'].self::SETTINGS_PATH;
		clearstatcache(true, $filename);
		$data = self::getFileContent($filename);
		if($data !== false)
		{
			return unserialize($data, [
				'allowed_classes' => false,
			]);
		}

		return false;
	}

	/**
	 * @param string $queueName
	 * @return string
	 */
	public static function getKeyForWorkersByQueueName($queueName)
	{
		return 'WORKERS_'.$queueName;
	}

	/**
	 * @param string $queueName
	 * @return int|null
	 */
	public function getLocalWorkersForQueue($queueName)
	{
		$this->data = static::getSettings();
		if (
			isset($this->data[static::getKeyForWorkersByQueueName($queueName)])
			&& $this->data[static::getKeyForWorkersByQueueName($queueName)] <> ''
		)
		{
			return intval($this->data[static::getKeyForWorkersByQueueName($queueName)]);
		}

		return null;
	}

	/**
	 * @return int
	 */
	protected function getWorkersTTL()
	{
		if(isset($this->data['workersTtl']))
		{
			$ttl = intval($this->data['workersTtl']);
			if($ttl > 0)
			{
				return $ttl;
			}
		}

		return static::DEFAULT_WORKERS_TTL;
	}

	/**
	 */
	protected function loadWorkersFromDatabase()
	{
		$this->workersCache = [];
		$this->data = static::getSettings();
		$this->lastTableAccess = time();

		$database = $this->getDatabase();
		$skipWarning = false;
		$errorReporting =
			\COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE)
			& ~E_STRICT
			& ~E_DEPRECATED
		;
		if($errorReporting & E_WARNING)
		{
			error_reporting($errorReporting & ~E_WARNING);
			$skipWarning = true;
		}
		$result = $database->Query('select * from b_transformercontroller_queue', true);
		if($skipWarning)
		{
			error_reporting($errorReporting);
		}
		if(!$result)
		{
			$database->Disconnect();
			$database->Connect($database->DBHost, $database->DBName, $database->DBLogin, $database->DBPassword);
			$result = $database->Query('select * from b_transformercontroller_queue');
		}
		while($queue = $result->Fetch())
		{
			$this->workersCache[$queue['NAME']] = $queue['WORKERS'] ?? null;
		}
	}

	/**
	 * Returns array $queueName => Number of processes
	 *
	 * @return array
	 */
	public function getQueueWorkers()
	{
		if($this->lastTableAccess == 0 || time() - $this->lastTableAccess > $this->getWorkersTTL())
		{
			// get default settings from database
			$this->loadWorkersFromDatabase();
		}
		$processes = 0;
		foreach($this->workersCache as $queue => $workers)
		{
			// rewrite from local file if there is any value
			$localProcesses = $this->getLocalWorkersForQueue($queue);
			if($localProcesses !== null)
			{
				$this->workersCache[$queue] = $workers = $localProcesses;
			}
			$processes += $workers;
		}
		// recalculate processes proportionally to $this->maximumProcesses
		if($processes > 0 && $this->maximumProcesses > 0 && $processes != $this->maximumProcesses)
		{
			$ratio = $this->maximumProcesses / $processes;
			foreach($this->workersCache as $queue => $workers)
			{
				if($workers > 0)
				{
					$workers = round($workers * $ratio);
					if($workers == 0)
					{
						$workers = 1;
					}
				}
				$this->workersCache[$queue] = $workers;
			}
		}

		return $this->workersCache;
	}

	/**
	 * Read file and returns content.
	 *
	 * @param string $filename Path to file.
	 * @return bool|string
	 */
	public static function getFileContent($filename)
	{
		$content = '';
		$file = @fopen($filename, 'rb');
		if($file)
		{
			$content = fread($file, filesize($filename));
			fclose($file);
		}
		return $content;
	}

	/**
	 * Put content to the file.
	 *
	 * @param string $filename Path to file.
	 * @param string $content Data to save.
	 * @return bool
	 */
	public static function putFileContent($filename, $content)
	{
		if(!is_dir(dirname($filename)))
		{
			@mkdir(dirname($filename));
		}
		$file = @fopen($filename, 'wb');
		if($file)
		{
			$written = @fwrite($file, $content);
			fclose($file);
			if($written > 0)
			{
				return true;
			}
		}
		return false;
	}

	public static function deleteDirectory($path)
	{
		if(is_file($path) || is_link($path))
		{
			if(!@unlink($path))
			{
				return false;
			}
		}
		elseif(is_dir($path))
		{
			if($handle = opendir($path))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..")
					{
						continue;
					}

					self::deleteDirectory($path.DIRECTORY_SEPARATOR.$file);
				}
				closedir($handle);
			}
			if(!@rmdir($path))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public static function isUseAutoAck()
	{
		return (Option::get('transformercontroller', 'use_auto_ack') === 'Y');
	}

	private static function isRetryConnectionsOnFail(): bool
	{
		return (Option::get('transformercontroller', 'enable_connection_retry', 'N') === 'Y');
	}

	/**
	 * How many retry attempts should be made if a http request to a client portal has failed
	 *
	 * @param string $scenario - downloadFile|getUploadInfo|completeCommand
	 * @return int
	 */
	final public static function getMaxConnectionRetryAttemptsCount(string $scenario): int
	{
		if (!self::isRetryConnectionsOnFail())
		{
			return 0;
		}

		$settingsJson = Option::get('transformercontroller', 'connection_retry_max_attempts');
		$settings = [];
		if (!empty($settingsJson))
		{
			try
			{
				$settings = Json::decode($settingsJson);
			}
			catch (ArgumentException)
			{
			}
		}

		$count = isset($settings[$scenario]) && is_int($settings[$scenario]) ? $settings[$scenario] : 1;
		if ($count < 0)
		{
			$count = 0;
		}

		return $count;
	}

	/**
	 * @return int|null - null means that response should be saved uncut, 0 - that response should not be saved at all
	 */
	final public static function getResponseMaxLengthInLogs(): ?int
	{
		$option = (int)Option::get('transformercontroller', 'logs_max_response_length', 300);

		return $option >= 0 ? $option : null;
	}
}
