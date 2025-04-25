<?php

if (empty($argv[0]))
{
	die('workerd.php should start from console only');
}

$docRoot = null;

if (!empty($argv[1]) && is_string($argv[1]))
{
	$docRoot = $argv[1];
}

if (empty($docRoot) || !is_dir($docRoot))
{
	$docRoot = realpath(__DIR__.'/../../../../');
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/transformercontroller_use_daemon.lock'))
{
	require_once __DIR__ . '/../daemon/loader.php';

	spl_autoload_register(\Bitrix\TransformerController\Daemon\Loader::autoLoad(...));

	$config = (new \Bitrix\TransformerController\Daemon\Config\Resolver())->resolve(
		$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/transformercontroller_worker_settings.json',
		$argc > 1 && is_numeric($argv[1]) ? (int)$argv[1] : null,
	);

	$supervisor = new \Bitrix\TransformerController\Daemon\Process\Supervisor(
		$config,
		\Bitrix\TransformerController\Daemon\Log\LoggerFactory::getInstance()->create(
			$config,
			[
				'type' => 'supervisor',
			],
		),
	);

	$supervisor->start();
}
else
{
	declare(ticks = 1);

	define('NO_KEEP_STATISTIC', 'Y');
	define('NO_AGENT_STATISTIC','Y');
	define('NOT_CHECK_PERMISSIONS', true);
	define('DisableEventsCheck', true);
	define('NO_AGENT_CHECK', true);
	define('BX_CRONTAB', true);
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

	\Bitrix\Main\Loader::includeModule('transformercontroller');

	if($argc > 1)
	{
		$settings = new \Bitrix\TransformerController\Settings(intval($argv[1]));
	}
	else
	{
		$settings = new \Bitrix\TransformerController\Settings();
	}

	$queues = $settings->getQueueWorkers();
	$log = new \Bitrix\TransformerController\Log(true);
	$log::logger()->notice('Start sys_workerd.php', ['type' => 'sys_workerd', 'workers' => $queues, 'pid' => getmypid()]);

	\Bitrix\TransformerController\Settings::deleteDirectory(\Bitrix\TransformerController\Worker::getLockPath());

	$childPids = [];
	$isParent = true;
	$isRestart = true;

	/**
	 * @param int $pid
	 * @param \Bitrix\TransformerController\Log $log
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	function killWorker($pid, $log)
	{
		if(\Bitrix\TransformerController\Worker::getStatus($pid) == \Bitrix\TransformerController\Worker::STATUS_WORK)
		{
			$signal = new \Bitrix\TransformerController\Signal($pid);
			if(!$signal->add(\Bitrix\TransformerController\Signal::CODE_DIE))
			{
				if(!\Bitrix\TransformerController\Cron::killProcessByPid($pid))
				{
					$log::logger()->error(
						'Error trying to kill {killedPid}. Exit.',
						['type' => 'sys_workerd', 'killedPid' => $pid, 'pid' => getmypid()],
					);
				}
			}
		}
		else
		{
			\Bitrix\TransformerController\Worker::clearData($pid);
			\Bitrix\TransformerController\Cron::killProcessByPid($pid);
		}
	}

	pcntl_signal(SIGUSR1, function($signo)
	{
		global $childPids, $log, $isRestart;
		$isRestart = false;
		$log::logger()->notice('Send signal to all child processes', ['type' => 'sys_workerd', 'pid' => getmypid()]);
		foreach($childPids as $queue => $pids)
		{
			foreach($pids as $pid)
			{
				killWorker($pid, $log);
			}
		}
	}, false);

	$sleepCounter = 0;
	while(1)
	{
		pcntl_signal_dispatch();
		$queues = $settings->getQueueWorkers();
		foreach($queues as $queue => $workers)
		{
			// unset dead pid numbers
			if(isset($childPids[$queue]))
			{
				foreach($childPids[$queue] as $pid)
				{
					$res = pcntl_waitpid($pid, $status, WNOHANG);
					if($res != 0)
					{
						if($res == -1 || !pcntl_wifexited($status))
						{
							$log::logger()->error(
								'Child {childPid} finished with error.',
								['type' => 'sys_worderd', 'childPid' => $pid, 'pid' => getmypid()],
							);
						}
						unset($childPids[$queue][$res]);
						$childStatus = pcntl_wexitstatus($status);
						$log::logger()->info(
							'A Child {childPid} completed with status {childStatus}',
							['type' => "sys_worderd", 'childPid' => $pid, 'childStatus' => $childStatus, 'pid' => getmypid()]
						);
					}
				}
			}
			else
			{
				$childPids[$queue] = [];
			}
			// now adjust workers - restart
			$processes = count($childPids[$queue]);
			if($isRestart)
			{
				if($processes < $workers)
				{
					while($processes < $workers)
					{
						$pid = pcntl_fork();
						if($pid === 0)
						{
							global $DB, $CACHE_MANAGER;
							$CACHE_MANAGER = new \CCacheManager;
							$DBHost = $DB->DBHost;
							$DBName = $DB->DBName;
							$DBLogin = $DB->DBLogin;
							$DBPassword = $DB->DBPassword;
							$DB = new \CDatabase;
							$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword);

							$app = \Bitrix\Main\Application::getInstance();
							if ($app != null)
							{
								$con = $app->getConnection();
								if ($con != null)
									$con->connect();
							}

							$DB->DoConnect();
							$queueName = $queue;
							include($_SERVER['DOCUMENT_ROOT'].\Bitrix\TransformerController\Cron::WORKER_PATH);
							unset($queueName);
						}
						elseif ($pid > 0)
						{
							$childPids[$queue][$pid] = $pid;
						}
						else
						{
							$log::logger()->critical(
								'Error trying to fork worker process',
								['type' => 'sys_worderd', 'pid' => getmypid()],
							);
						}
						$processes++;
					}
				}
			}
			if($processes > $workers)
			{
				foreach($childPids[$queue] as $pid)
				{
					killWorker($pid, $log);
					$processes--;
					if($processes <= $workers)
					{
						break;
					}
				}
			}
		}

		sleep(1);
		$sleepCounter++;
		if($sleepCounter >= 50)
		{
			\Bitrix\TransformerController\Cron::killSlowProcesses();
			$sleepCounter = 0;
		}

		if(!$isRestart)
		{
			$processes = 0;
			foreach($childPids as $pids)
			{
				$processes += array_sum($pids);
			}

			if($processes == 0)
			{
				break;
			}
		}
	}

	$log::logger()->notice('All children has finished, bye', ['type' => 'sys_workerd', 'pid' => getmypid()]);
}
