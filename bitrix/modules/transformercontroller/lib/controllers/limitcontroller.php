<?php

namespace Bitrix\TransformerController\Controllers;

use Bitrix\TransformerController\Entity\LimitsTable;
use Bitrix\TransformerController\Limits;
use Bitrix\Main\Error;
use Bitrix\TransformerController\Queue;

class LimitController extends Base
{
	protected function getActionList()
	{
		return [
			'getList' => [
				'params' => ['domain', 'tarif', 'commandName', 'queueName', 'type'],
				'permissions' => ['admin'],
			],
			'add' => [
				'params' => ['domain', 'tarif', 'commandName', 'period', 'count', 'fileSize', 'queueName', 'type'],
				'permissions' => ['admin'],
			],
			'delete' => [
				'params' => ['id', 'domain', 'tarif', 'type'],
				'permissions' => ['admin'],
			],
			'clear' => [
				'permissions' => ['admin'],
			],
			'usage' => [
				'params' => ['domain' => ['required' => true], 'period', 'commandName', 'queueName'],
				'permissions' => ['admin'],
			],
		];
	}

	protected function getList($params)
	{
		$limits = new Limits($params);
		$this->result->setData($limits->getList());
	}

	protected function add($params)
	{
		$data = [];
		if(isset($params['queueName']))
		{
			$queueId = Queue::getQueueIdByName($params['queueName']);
			if(!$queueId)
			{
				$this->result->addError(new Error('queue with name '.$params['queueName'].' not found'));
				return false;
			}
			else
			{
				$data['QUEUE_ID'] = $queueId;
				unset($params['queueName']);
			}
		}
		$map = Limits::getMap();
		$map = array_flip($map);
		foreach($params as $param => $value)
		{
			if(!empty($value) && isset($map[$param]))
			{
				$data[$map[$param]] = $value;
			}
		}
		$this->result = LimitsTable::add($data);
	}

	protected function delete($params)
	{
		if(isset($params['id']))
		{
			$this->result = LimitsTable::delete($params['id']);
		}
		elseif(!empty($params['domain']) || !empty($params['tarif']) || !empty($params['type']))
		{
			$this->_delete($params);
		}
		else
		{
			$this->result->addError(new Error('id or domain or tarif should be specified'));
		}
	}

	protected function clear($params)
	{
		$this->_delete();
	}

	protected function _delete($params = [])
	{
		$limits = new Limits($params);
		$list = $limits->getList(true);
		$deleted = 0;
		foreach($list as $limit)
		{
			$result = LimitsTable::delete($limit['ID']);
			if($result->isSuccess())
			{
				$deleted++;
			}
			else
			{
				$this->result->addErrors($result->getErrors());
			}
		}
		$this->result->setData(['deleted' => $deleted]);
	}

	protected function usage($params)
	{
		$limits = new Limits($params);
		$this->result->setData($limits->getUsage($params['period']));
	}
}