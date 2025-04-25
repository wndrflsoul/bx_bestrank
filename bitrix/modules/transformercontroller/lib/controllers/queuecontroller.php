<?php

namespace Bitrix\TransformerController\Controllers;

use Bitrix\Main\Error;
use Bitrix\TransformerController\Entity\QueueTable;
use Bitrix\TransformerController\Settings;

class QueueController extends Base
{
	protected function getActionList()
	{
		return [
			'getList' => [
				'params' => ['isLocal'],
				'permissions' => ['admin'],
			],
			'add' => [
				'params' => ['name' => ['required' => true], 'workers' => ['required' => true], 'sort', 'isLocal'],
				'permissions' => ['admin'],
			],
			'delete' => [
				'params' => ['name' => ['required' => true], 'isLocal'],
				'permissions' => ['admin'],
			],
		];
	}

	protected function getList($params)
	{
		$isLocal = (isset($params['isLocal']) && $params['isLocal'] == 'y');
		$result = [];
		$queues = QueueTable::getList()->fetchAll();
		$settings = new Settings();
		foreach($queues as $queue)
		{
			if($isLocal)
			{
				$queue['WORKERS'] = $settings->getLocalWorkersForQueue($queue['NAME']);
			}
			$result[$queue['NAME']] = $queue;
		}
		$this->result->setData($result);
	}

	protected function add($params)
	{
		$isLocal = (isset($params['isLocal']) && $params['isLocal'] == 'y');
		if($isLocal)
		{
			$settings = Settings::getSettings();
			$settings[Settings::getKeyForWorkersByQueueName($params['name'])] = $params['workers'];
			Settings::saveSettings($settings);
		}
		else
		{
			$data = [
				'NAME' => $params['name'],
				'WORKERS' => $params['workers'],
			];
			if(isset($params['sort']) && $params['sort'] > 0)
			{
				$data['SORT'] = $params['sort'];
			}
			$queue = QueueTable::getList(['filter' => ['=NAME' => $params['name']]])->fetch();
			if($queue)
			{
				$result = QueueTable::update($queue['ID'], $data);
			}
			else
			{
				$result = QueueTable::add($data);
			}
			if($result->isSuccess())
			{
				$this->result->setData(['id' => $result->getId()]);
			}
			else
			{
				$this->result = $result;
			}
		}
	}

	protected function delete($params)
	{
		$isLocal = (isset($params['isLocal']) && $params['isLocal'] == 'y');
		if($isLocal)
		{
			$settings = Settings::getSettings();
			unset($settings[Settings::getKeyForWorkersByQueueName($params['name'])]);
			Settings::saveSettings($settings);
		}
		else
		{
			$queue = QueueTable::getList(['filter' => ['=NAME' => $params['name']]])->fetch();
			if($queue)
			{
				$this->result = QueueTable::delete($queue['ID']);
			}
			else
			{
				$this->result->addError(new Error('queue with name '.$params['name'].' not found'));
			}
		}
	}
}