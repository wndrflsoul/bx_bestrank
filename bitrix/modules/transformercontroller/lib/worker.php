<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Error;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\TransformerController\Runner\Runner;
use Bitrix\TransformerController\Runner\SystemRunner;

class Worker
{
	const LOCK_FILE_PATH = '/worker_lock/';
	const STATUS_WORK = 'work';

	protected $queue;
	protected $fileUploader;
	protected $httpClass;
	protected $endTime;
	protected $runner;

	/**
	 * Worker constructor.
	 *
	 * @param Queue $queue Physical queue we are working with.
	 * @param FileUploader $fileUploader Object to download and upload files to the client directly.
	 * @param string $httpClass Class to query requests back to the client.
	 *          We do not use an object here, because we need several requests and HttpClient works fine for one request only.
	 * @param int $endTime Time in seconds after which worker should die.
	 * @throws ArgumentTypeException
	 */
	public function __construct(Queue $queue, FileUploader $fileUploader, $httpClass, $endTime = 0)
	{
		if(!is_a($httpClass, '\Bitrix\Main\Web\HttpClient', true))
		{
			throw new ArgumentTypeException('httpClass', '\Bitrix\Main\Web\HttpClient');
		}
		$this->queue = $queue;
		$this->fileUploader = $fileUploader;
		$this->httpClass = $httpClass;
		$this->endTime = $endTime;
		$this->runner = $this->getRunner();
		Log::logger()->info(
			'worker for queue {queue} has started at {date} end time is {endTimestamp}',
			[
				'type' => 'worker',
				'queue' => $queue->getName(),
				'endTimestamp' => $endTime,
				'pid' => getmypid(),
			]
		);
	}

	/**
	 * Blocking method. Binds messages from the queue and function to process them.
	 *
	 * @return void
	 */
	final public function work()
	{
		$this->queue->processMessageWith(array($this, 'processCommand'));
	}

	/**
	 * This method:
	 *      process one message from the queue
	 *      sends result to the client
	 *      delete message from the queue
	 *      die if it is time for it
	 * All of it in one place because \AMQPQueue::consume behavior.
	 *
	 * @param string $commandName Class name, should extends BaseCommand.
	 * @param array $params Parameters to $command constructor.
	 * @param array $usageInfo Information about statistic.
	 * @param string $messageId
	 * @return void
	 */
	public function processCommand($commandName, $params, array $usageInfo, $messageId = '')
	{
		$this->setStatus(self::STATUS_WORK);

		$usageInfo['GUID'] ??= null;
		$usageInfo['DOMAIN'] ??= null;
		$usageInfo['LICENSE_KEY'] ??= null;
		$usageInfo['TIME'] ??= null;
		$usageInfo['QUEUE_ID'] ??= null;
		$usageInfo['TARIF'] ??= null;

		Log::logger()->info(
			'get {commandName} with id {guid} from queue {queue}',
			[
				'type' => 'worker',
				'commandName' => $commandName,
				'guid' => $usageInfo['GUID'],
				'queue' => $this->queue->getName(),
				'pid' => getmypid(),
			],
		);
		if($messageId)
		{
			$this->queue->deleteMessage($messageId);
		}
		$statisticData = [
			'COMMAND_NAME' => $commandName,
			'DOMAIN' => $usageInfo['DOMAIN'],
			'LICENSE_KEY' => $usageInfo['LICENSE_KEY'],
			'TIME_ADD' => DateTime::createFromTimestamp($usageInfo['TIME']),
			'TIME_START' => time() - $usageInfo['TIME'],
			'ERROR' => TimeStatistic::ERROR_CODE_COMMAND_FAILED,
			'QUEUE_ID' => $usageInfo['QUEUE_ID'],
			'GUID' => $usageInfo['GUID'],
		];
		$ban = Cron::tryInvokeWithRestoringConnection(function() use ($usageInfo) {
			return BanList::getByDomain($usageInfo['DOMAIN']);
		});
		$backUrl = $params['back_url'] ?? null;
		if($ban)
		{
			$statisticData['ERROR'] = TimeStatistic::ERROR_CODE_DOMAIN_IS_BANNED;
			$statisticData['ERROR_INFO'] = 'Domain '.$usageInfo['DOMAIN'].' is banned: '.$ban['REASON'];
			$this->error(
				$statisticData['ERROR_INFO'],
				'',
				['errorCode' => $statisticData['ERROR']],
				['guid' => $usageInfo['GUID'] ?? null],
			);
		}
		elseif(is_a($commandName, BaseCommand::getClassName(), true))
		{
			/* @var BaseCommand $command */
			$command = new $commandName($params, $this->runner, $this->fileUploader);

			$this->executeCommand($command, $backUrl, $usageInfo, $statisticData);
		}
		else
		{
			$statisticData['ERROR'] = TimeStatistic::ERROR_CODE_COMMAND_NOT_FOUND;
			$statisticData['ERROR_INFO'] = 'command '.$commandName.' not found';
			$this->error(
				$statisticData['ERROR_INFO'],
				$backUrl,
				[
					'errorCode' => $statisticData['ERROR'],
				],
				['guid' => $usageInfo['GUID'] ?? null]
			);
		}

		$this->fileUploader->deleteFiles();

		$statisticData['TIME_END'] = time() - $usageInfo['TIME'];
		$statisticData['TIME_END_ABSOLUTE'] = DateTime::createFromTimestamp(time());
		Cron::tryInvokeWithRestoringConnection(function() use ($statisticData) {
			TimeStatistic::add($statisticData);
		});
		static::clearStatus();
		if($this->isTimeToFinish())
		{
			$this->finish();
		}
	}

	/**
	 * Send final message to the client.
	 *
	 * @param string $url Where to send.
	 * @param array $post Additional parameters to send.
	 * @return bool|string
	 */
	protected function completeCommand($url, $post = [])
	{
		$post['finish'] = 'y';

		[, $result] = Http::sendWithRetry('completeCommand', function () use ($url, $post): array {
			/* @var HttpClient $http */
			$http = new $this->httpClass(['socketTimeout' => 4, 'streamTimeout' => 300]);
			Log::configureLogging($http);
			$result = $http->post($url, $post);

			return [$http, $result];
		});

		return $result;
	}

	/**
	 * Write to log error, and send it to the client.
	 *
	 * @param string|string[]|Error[] $error Some debug information.
	 * @param string $backUrl Back url to send error to callback.
	 * @param array $data
	 * @return bool|string
	 */
	protected function error($error, $backUrl = '', array $data = [], array $logData = [])
	{
		if (isset($data['errorCode']))
		{
			$logData['errorCode'] = $data['errorCode'];
		}

		if (is_array($error))
		{
			$errorObjects = array_filter($error, fn($value) => $value instanceof Error);
			if (empty($errorObjects)) // array of strings
			{
				$error = implode(PHP_EOL, $error);
			}
			else
			{
				$messages = [];

				foreach ($errorObjects as $singleError)
				{
					$messages[] = $singleError->getMessage();

					if (is_array($singleError->getCustomData()))
					{
						$logData += $singleError->getCustomData();
					}
				}

				$error = implode(PHP_EOL, $messages);
			}
		}

		Log::logger()->error(
			'{error}',
			['type' => 'worker', 'error' => $error, 'pid' => getmypid()] + $logData,
		);

		if ($backUrl)
		{
			$data['error'] = $error;
			return $this->completeCommand($backUrl, $data);
		}
		return true;
	}

	/**
	 * Final actions before exit.
	 *
	 */
	protected function finish()
	{
		self::clearData();
		$this->fileUploader->deleteFiles();
		unset($this->queue);
		/** @noinspection PhpUndefinedClassInspection */
		\CMain::finalActions();
		exit();
	}

	/**
	 * Clear worker data by pid.
	 *
	 * @param int $pid
	 */
	public static function clearData($pid = null)
	{
		if(!$pid)
		{
			$pid = getmypid();
		}
		Settings::deleteDirectory(Document::getLibreOfficeConfigUserPath($pid));
		self::clearStatus($pid);
		Log::logger()->info('worker has finished', ['type' => 'worker', 'pid' => getmypid()]);
	}

	/**
	 * Returns true if worker should finish.
	 *
	 * @return bool
	 */
	protected function isTimeToFinish()
	{
		if(function_exists('pcntl_signal_dispatch'))
		{
			pcntl_signal_dispatch();
		}
		$signal = new Signal(getmypid());
		if($signal->get() == Signal::CODE_DIE)
		{
			return true;
		}
		if(time() > $this->endTime)
		{
			return true;
		}

		return false;
	}

	/**
	 * Set time to die for the worker (timestamp).
	 *
	 * @param int $endTime
	 */
	public function setEndTime($endTime = 0)
	{
		$endTime = intval($endTime);
		$this->endTime = $endTime;
	}

	public static function getLockPath(): string
	{
		return Path::combine(
			FileUploader::provideLocalUploadPath(),
			static::LOCK_FILE_PATH
		);
	}

	/**
	 * Returns path to the lock file of the worker.
	 *
	 * @param int $pid
	 * @return string
	 */
	protected static function getPath($pid = null)
	{
		if(!$pid)
		{
			$pid = getmypid();
		}

		return Path::combine(static::getLockPath(), $pid.'.lock');
	}

	/**
	 * Save status of this worker in lock file.
	 *
	 * @param string $status
	 */
	protected function setStatus($status)
	{
		Settings::putFileContent($this->getPath(), $status);
	}

	/**
	 * Get status of the worker by $pid.
	 *
	 * @param int $pid
	 * @return bool|string
	 */
	public static function getStatus($pid)
	{
		return Settings::getFileContent(self::getPath($pid));
	}

	/**
	 * Clears lock file of the worker.
	 *
	 * @param int $pid
	 */
	public static function clearStatus($pid = null)
	{
		if(!$pid)
		{
			$pid = getmypid();
		}
		if(file_exists(self::getPath($pid)))
		{
			@unlink(self::getPath($pid));
		}
	}

	/**
	 * @return SystemRunner
	 */
	protected function getRunner()
	{
		if(defined('BX_TC_RUNNER_CLASSNAME'))
		{
			$runnerClass = BX_TC_RUNNER_CLASSNAME;
			if($runnerClass && is_a($runnerClass, Runner::class, true))
			{
				return new $runnerClass;
			}
		}

		return new SystemRunner();
	}

	private function executeCommand(
		BaseCommand $command,
		?string $backUrl,
		array $usageInfo,
		array &$statisticData
	): void
	{
		$this->fileUploader->setUrl($backUrl);
		$this->fileUploader->setMaxDownloadSize($command::getMaxFileSize($usageInfo['TARIF']));

		$executeResult = $command->execute();

		$resultData = $executeResult->getData();
		$downloadTime = $resultData['downloadTime'] ?? null;

		$statisticData['FILE_SIZE'] = $this->fileUploader->getFileSize();
		$statisticData['TIME_EXEC'] = time() - $statisticData['TIME_START'] - $usageInfo['TIME'] - (int)$downloadTime;
		$statisticData['TIME_DOWNLOAD'] = $downloadTime;

		$this->fileUploader->setFiles($resultData['files'] ?? []);

		if (!$executeResult->isSuccess())
		{
			$errors = $executeResult->getErrors();
			$lastError = array_pop($errors);
			$statisticData['ERROR'] = $lastError->getCode();
			$statisticData['ERROR_INFO'] = print_r($executeResult->getErrorMessages(), 1);
			$this->error(
				$executeResult->getErrors(),
				$backUrl,
				[
					'errorCode' => $statisticData['ERROR'],
				],
				['guid' => $usageInfo['GUID'] ?? null]
			);

			return;
		}

		if (!empty($resultData['files']))
		{
			$uploadResult = $this->fileUploader->uploadFiles();

			$statisticData['TIME_UPLOAD'] = time() - $statisticData['TIME_EXEC'] - $statisticData['TIME_START'] - $usageInfo['TIME'];

			if ($uploadResult->isSuccess())
			{
				$resultData['files'] = $uploadResult->getData();
			}
			else
			{
				$executeResult->addErrors($uploadResult->getErrors());

				$statisticData['ERROR'] = TimeStatistic::ERROR_CODE_UPLOAD_FILES;
				$statisticData['ERROR_INFO'] = print_r($uploadResult->getErrorMessages(), 1);
				$this->error(
					$executeResult->getErrors(),
					$backUrl,
					[
						'result' => [
							'files' => $uploadResult->getData(),
						],
						'errorCode' => $statisticData['ERROR'],
					],
					['guid' => $usageInfo['GUID'] ?? null]
				);

				return;
			}
		}

		$statisticData['ERROR'] = 0;
		if (isset($resultData['error']))
		{
			$statisticData['ERROR'] = (int)$resultData['error'];
			unset($resultData['error']);
		}
		if (empty($resultData['files']))
		{
			unset($resultData['files']);
		}

		$response = $this->completeCommand($backUrl, ['result' => $resultData]);

		Log::logger()->info(
			'Completed command',
			[
				'type' => 'worker',
				'responseOnComplete' => mb_substr($response, 0, Settings::getResponseMaxLengthInLogs()),
				'pid' => getmypid(),
				'guid' => $usageInfo['GUID'] ?? null,
			],
		);
	}
}
