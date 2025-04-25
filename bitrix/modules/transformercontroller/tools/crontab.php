<?php

// this file for crontab-usage only

if(!isset($argv[0]) || empty($argv[0]))
{
	die('workerd.php should start from console only');
}

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../../');

/** @noinspection PhpIncludeInspection */
require_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/transformercontroller/lib/settings.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/transformercontroller/lib/cron.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/transformercontroller/lib/log.php';

\Bitrix\TransformerController\Cron::killSlowProcesses();

if($argc > 1)
{
	$processesNeeded = intval($argv[1]);
	if($processesNeeded > 0)
	{
		$settings = \Bitrix\TransformerController\Settings::getSettings();
		$settings['processes'] = $processesNeeded;
		\Bitrix\TransformerController\Settings::saveSettings($settings);
	}
}
else
{
	$settings = \Bitrix\TransformerController\Settings::getSettings();
	$processesNeeded = $settings['processes'];
}

$pids = \Bitrix\TransformerController\Cron::getWorkerPids();
$processes = count($pids);
if($processes > $processesNeeded)
{
	while($processes > $processesNeeded)
	{
		echo 'kill worker';
		\Bitrix\TransformerController\Cron::killWorker($pids[$processes]);
		$processes--;
	}
}
else
{
	while($processes < $processesNeeded)
	{
		echo 'start worker';
		\Bitrix\TransformerController\Cron::startWorker();
		$processes++;
	}
}