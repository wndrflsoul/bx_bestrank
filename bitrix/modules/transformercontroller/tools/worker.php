<?php

use Bitrix\Main\Config\Option;
use Bitrix\TransformerController\Queue;
use Bitrix\TransformerController\Worker;

declare(ticks = 1);

if (empty($argv[0]))
{
	die('worker.php should start from console only');
}

if (empty($queueName))
{
	die('No queue name');
}

if (empty($docRoot) || !is_dir($docRoot))
{
	$docRoot = realpath(__DIR__.'/../../../../');
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;

if (!defined('NO_KEEP_STATISTIC'))
{
	define('NO_KEEP_STATISTIC', 'Y');
}
if (!defined('NO_AGENT_STATISTIC'))
{
	define('NO_AGENT_STATISTIC', 'Y');
}
if (!defined('NOT_CHECK_PERMISSIONS'))
{
	define('NOT_CHECK_PERMISSIONS', true);
}
if (!defined('DisableEventsCheck'))
{
	define('DisableEventsCheck', true);
}
if (!defined('NO_AGENT_CHECK'))
{
	define('NO_AGENT_CHECK', true);
}
if (!defined('BX_CRONTAB'))
{
	define('BX_CRONTAB', true);
}

/** @noinspection PhpIncludeInspection */
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(!\Bitrix\Main\Loader::includeModule('transformercontroller'))
{
	die ('module transformercontroller is not installed');
}

\Bitrix\TransformerController\Cron::changeDirectory(\Bitrix\TransformerController\FileUploader::provideLocalUploadPath());

$exchange = Queue::createExchange($queueName);
$queue = new Queue($exchange, new \AMQPQueue($exchange->getChannel()), $queueName);

$startTime = time();
$workTimeFrom = (int)Option::get('transformercontroller', 'lifetime_from', 5);
$workTimeTo = (int)Option::get('transformercontroller', 'lifetime_to', 10);
$workTime = random_int($workTimeFrom, $workTimeTo) * 60;
$endTime = $startTime + $workTime;

$fileUploader = new \Bitrix\TransformerController\FileUploader();

$worker = new Worker($queue, $fileUploader, '\Bitrix\Main\Web\HttpClient', $endTime);
pcntl_signal(SIGUSR1, [$worker, 'setEndTime'], false);
$worker->work();
