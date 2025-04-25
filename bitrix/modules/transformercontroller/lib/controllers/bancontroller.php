<?php

namespace Bitrix\TransformerController\Controllers;

use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\TransformerController\BanList;
use Bitrix\TransformerController\Queue;

class BanController extends Base
{
	protected function getActionList()
	{
		return [
			'getList' => [
				'params' => ['all'],
				'permissions' => ['admin'],
			],
			'getListForWorker' => [
				'permissions' => ['daemon'],
			],
			'add' => [
				'params' => ['domain' => ['required' => true], 'date_end', 'reason', 'queue'],
				'permissions' => ['admin'],
			],
			'delete' => [
				'params' => ['domain' => ['required' => true], 'queue'],
				'permissions' => ['admin'],
			],
		];
	}

	protected function getList($params)
	{
		$list = BanList::getList($params['all']);
		foreach($list as &$ban)
		{
			/** @var DateTime $date */
			$date = $ban['DATE_ADD'];
			$ban['DATE_ADD'] = $date->getTimestamp();
			$date = $ban['DATE_END'];
			$ban['DATE_END'] = $date->getTimestamp();
		}
		$this->result->setData($list);
	}

	protected function getListForWorker(): void
	{
		$result = [];
		foreach (BanList::getList() as $ban)
		{
			$banItem = [
				'domain' => $ban['DOMAIN'],
				'queueName' => $ban['QUEUE_NAME'] ?? null,
			];

			/** @var DateTime|null $dateEnd */
			$dateEnd = $ban['DATE_END'] ?? null;
			if ($dateEnd)
			{
				$banItem['isPermanent'] = false;
				$banItem['dateEndTimestamp'] = $dateEnd->getTimestamp();
			}
			else
			{
				$banItem['isPermanent'] = true;
				$banItem['dateEndTimestamp'] = null;
			}

			$result[] = $banItem;
		}

		$this->result->setData($result);
	}

	protected function add($params)
	{
		$data = [
			'DOMAIN' => $params['domain'],
			'DATE_END' => DateTime::createFromTimestamp($params['date_end']),
			'REASON' => $params['reason'],
		];
		if(!empty($params['queue']))
		{
			$queueId = Queue::getQueueIdByName($params['queue']);
			if(!$queueId)
			{
				$this->result->addError(new Error('queue with name '.$params['queue'].' not found'));
				return false;
			}
			else
			{
				$data['QUEUE_ID'] = $queueId;
			}
		}
		$addResult = BanList::add($data);
		if($addResult->isSuccess())
		{
			$this->result->setData(['ID' => $addResult->getId()]);
		}
		else
		{
			$this->result = $addResult;
		}
	}

	protected function delete($params)
	{
		$this->result = BanList::delete($params['domain'], $params['queue']);
	}
}
