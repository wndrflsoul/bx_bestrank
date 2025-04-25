<?php

namespace Bitrix\TransformerController\Controllers;

use Bitrix\Main\DB\SqlException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\TransformerController\Entity\TimeStatisticTable;
use Bitrix\TransformerController\Entity\UsageStatisticTable;
use Bitrix\TransformerController\Queue;
use Bitrix\TransformerController\TimeStatistic;

class StatisticController extends Base
{
	protected function getActionList()
	{
		return [
			'statistic' => [
				'params' => [
					'period' => ['default' => 60],
					'queue',
					'date',
					'dateFrom',
					'dateTo',
				],
			],
			'top' => [
				'params' => [
					'period' => ['default' => 60],
					'limit' => ['default' => 5],
					'queue',
					'date',
					'dateFrom',
					'dateTo',
				],
			],
			'percentiles' => [
				'permissions' => ['admin'],
				'params' => [
					'period' => ['default' => 60],
					'times' => ['default' => 'full_time'],
					'isAllTimes',
					'percentiles' => ['default' => 0.95],
					'command',
					'queue',
					'date',
					'dateFrom',
					'dateTo',
				],
			],
			'add' => [
				'permissions' => ['daemon'],
				'params' => [
					'guid' => ['required' => true],

					'fileSize',

					'errorCode',
					'error',

					'startTimestamp' => ['required' => true],
					'endTimestamp' => ['required' => true],

					'timeDownload',
					'timeExec',
					'timeUpload',
				],
			],
		];
	}

	protected function getPeriodsFromRequest(array $params)
	{
		$period = intval($params['period']);
		$periodStart = $periodEnd = null;
		if(isset($params['date']))
		{
			$date = DateTime::tryParse($params['date'], 'Y-m-d');
			if($date)
			{
				$periodStart = $date;
				$periodEnd = clone $date;
				$periodEnd->add('1D');
			}
		}
		elseif(isset($params['dateFrom']) && isset($params['dateTo']))
		{
			$periodStart = DateTime::tryParse($params['dateFrom'], 'Y-m-d-H-i');
			$periodEnd = DateTime::tryParse($params['dateTo'], 'Y-m-d-H-i');
		}

		if(!$periodStart || !$periodEnd)
		{
			$periodEnd = DateTime::createFromTimestamp(time());
			$periodStart = DateTime::createFromTimestamp(time() - $period);
		}

		return [$periodStart, $periodEnd];
	}

	private function getTimeDBColumnsFromRequest(array $params): array
	{
		$allowed = array_values(TimeStatistic::getTimeDBColumnToJsonFieldMap());

		if (!empty($params['isAllTimes']))
		{
			return TimeStatistic::mapJsonFieldsToTimeDBColumns($allowed);
		}

		if (empty($params['times']) || !is_array($params['times']))
		{
			return TimeStatistic::mapJsonFieldsToTimeDBColumns(['full_time']);
		}

		$filtered = array_filter($params['times'], fn(mixed $value) => in_array($value, $allowed, true));

		return TimeStatistic::mapJsonFieldsToTimeDBColumns($filtered);
	}

	private function getPercentilesFromRequest(array $params): array
	{
		if (empty($params['percentiles']) || !is_array($params['percentiles']))
		{
			return [0.95];
		}

		$percentiles = [];
		foreach ($params['percentiles'] as $percentile)
		{
			$percentile = (float)$percentile;
			if ($percentile > 0 && $percentile < 1)
			{
				$percentiles[] = $percentile;
			}
		}

		sort($percentiles);

		return array_unique($percentiles);
	}

	private function getCommandsFromRequest(array $params): array
	{
		$commands = [$params['command'] ?? null];

		$commandsFiltered = array_filter(
			$commands,
			fn(mixed $value) => in_array($value, TimeStatistic::getMapFields(), true),
		);
		if (empty($commandsFiltered))
		{
			$commandsFiltered = ['document', 'video'];
		}

		return TimeStatistic::mapJsonFieldsToCommandNames($commandsFiltered);
	}

	protected function statistic($params)
	{
		$filter = [];
		if(isset($params['queue']))
		{
			$queueId = Queue::getQueueIdByName($params['queue']);
			if(!$queueId)
			{
				$this->result->addError(new Error('queue with name '.$params['queue'].' not found'));
				return false;
			}
			else
			{
				$filter['=QUEUE_ID'] = $queueId;
			}
		}

		[$periodStart, $periodEnd] = $this->getPeriodsFromRequest($params);

		$timeStatistic = TimeStatistic::get($periodStart, $periodEnd, $filter);

		$errorsCount = TimeStatistic::getErrorsCount($periodStart, $periodEnd, $filter);

		$timeStatistic = TimeStatistic::formatJson($timeStatistic, $errorsCount);

		$usageStatistic = UsageStatisticTable::getList([
			'select' => [
				new ExpressionField('COUNT', 'COUNT(*)'),
				'COMMAND_NAME',
			],
			'filter' => array_merge([
				'>DATE' => $periodStart,
				'<DATE' => $periodEnd,
			], $filter),
			'group' => ['COMMAND_NAME']
		])->fetchAll();

		foreach($usageStatistic as $command)
		{
			$timeStatistic[TimeStatistic::getJsonField($command['COMMAND_NAME'])]['added_count'] = $command['COUNT'];
		}

		return $timeStatistic;
	}

	protected function top($params)
	{
		[$periodStart, $periodEnd] = $this->getPeriodsFromRequest($params);
		$limit = intval($params['limit']);
		$filter = [
			'>DATE' => $periodStart,
			'<DATE' => $periodEnd,
		];
		if(isset($params['queue']))
		{
			$queueId = Queue::getQueueIdByName($params['queue']);
			if(!$queueId)
			{
				$this->result->addError(new Error('queue with name '.$params['queue'].' not found'));
				return false;
			}
			else
			{
				$filter['=QUEUE_ID'] = $queueId;
			}
		}

		$result = [];
		$topList = UsageStatisticTable::getList([
			'select' => [
				new ExpressionField('commands', 'COUNT(*)'),
				new ExpressionField('size', 'SUM(%s)', ['FILE_SIZE']),
				'DOMAIN',
			],
			'order' => [
				'commands' => 'DESC'
			],
			'filter' => $filter,
			'group' => ['DOMAIN'],
			'limit' => $limit,
		]);
		while($top = $topList->fetch())
		{
			$top['file_size'] = $top['size'];
			$top['domain'] = $top['DOMAIN'];
			unset($top['DOMAIN']);
			unset($top['size']);
			$result[] = $top;
		}

		return $result;
	}

	protected function percentiles($params)
	{
		$filter = $this->getPercentilesDatasetFilter($params);
		if ($filter === null)
		{
			return false;
		}

		$timeColumns = $this->getTimeDBColumnsFromRequest($params);
		if (empty($timeColumns))
		{
			return false;
		}

		$percentiles = $this->getPercentilesFromRequest($params);

		$result = [];
		foreach ($this->getCommandsFromRequest($params) as $commandName)
		{
			foreach ($percentiles as $percentile)
			{
				$values = $this->calculatePercentileValue($percentile, $timeColumns, $filter + ['=COMMAND_NAME' => $commandName]);

				foreach ($values as $dbColumn => $value)
				{
					$result[TimeStatistic::getJsonField($commandName)][TimeStatistic::mapTimeDBColumnToJsonField($dbColumn)][(string)$percentile] = $value;
				}
			}
		}

		return $result;
	}

	private function getPercentilesDatasetFilter(array $params): ?array
	{
		[$periodStart, $periodEnd] = $this->getPeriodsFromRequest($params);

		$filter = [
			'>TIME_END_ABSOLUTE' => $periodStart,
			'<TIME_END_ABSOLUTE' => $periodEnd,
		];
		if(isset($params['queue']))
		{
			$queueId = Queue::getQueueIdByName($params['queue']);
			if(!$queueId)
			{
				$this->result->addError(new Error('queue with name '.$params['queue'].' not found'));
				return null;
			}
			else
			{
				$filter['=QUEUE_ID'] = $queueId;
			}
		}

		return $filter;
	}

	private function calculatePercentileValue(float $percentile, array $timeColumns, array $filter): ?array
	{
		$reversedPercentile = 1 - $percentile;
		if ($reversedPercentile <= 0 || $reversedPercentile >= 1)
		{
			return null;
		}

		$offsetDbRow = TimeStatisticTable::query()
			->setSelect([
				'CNT',
				new ExpressionField('DATASET_OFFSET', "FLOOR($reversedPercentile * %s)", ['CNT']),
			])
			->setFilter($filter)
			->registerRuntimeField(new ExpressionField('CNT', 'COUNT(*)'))
			->fetch()
		;

		if ((int)$offsetDbRow['CNT'] <= 0)
		{
			// no data by this filter, skip queries
			return [];
		}

		$result = [];
		foreach ($timeColumns as $singleTimeColumn)
		{
			$row = TimeStatisticTable::query()
				->setSelect([$singleTimeColumn])
				->setFilter($filter)
				->addOrder($singleTimeColumn, 'DESC')
				->setLimit(1)
				->setOffset($offsetDbRow['DATASET_OFFSET'])
				->fetch()
			;

			$result[$singleTimeColumn] = $row[$singleTimeColumn];
		}

		return $result;
	}

	protected function add(array $params): void
	{
		$usageInfo = UsageStatisticTable::query()
			// this data will be used to fill time statistic fields
			->setSelect([
				'COMMAND_NAME',
				'DOMAIN',
				'LICENSE_KEY',
				'TIME_ADD' => 'DATE',
				'QUEUE_ID',
				'GUID',
			])
			->where('GUID', $params['guid'])
			->setLimit(1)
			->fetch()
		;

		if (empty($usageInfo))
		{
			$this->result->addError(new Error('Command with this GUID not found'));

			return;
		}

		if ($usageInfo['TIME_ADD'] instanceof DateTime)
		{
			$addedTimestamp = $usageInfo['TIME_ADD']->getTimestamp();
		}
		else
		{
			$addedTimestamp = (new DateTime($usageInfo['TIME_ADD']))->getTimestamp();
		}

		try
		{
			$addResult = TimeStatistic::add([
				'PROCESSED_BY' => $this->daemonGuid,
				'FILE_SIZE' => $params['fileSize'],
				'ERROR' => $params['errorCode'],
				'ERROR_INFO' => $params['error'],
				'TIME_START' => $params['startTimestamp'] - $addedTimestamp,
				'TIME_DOWNLOAD' => $params['timeDownload'],
				'TIME_EXEC' => $params['timeExec'],
				'TIME_UPLOAD' => $params['timeUpload'],
				'TIME_END_ABSOLUTE' => DateTime::createFromTimestamp($params['endTimestamp']),
				'TIME_END' => $params['endTimestamp'] - $addedTimestamp,
				...$usageInfo,
			]);
		}
		catch (SqlException $exception)
		{
			if (
				str_contains($exception->getMessage(), 'Duplicate')
				&& str_contains($exception->getMessage(), $params['guid'])
			)
			{
				$this->result->addError(new Error('Statistic for this command already registered'));

				return;
			}

			throw $exception;
		}

		if (!$addResult->isSuccess())
		{
			$this->result->addErrors($addResult->getErrors());
		}
	}
}
