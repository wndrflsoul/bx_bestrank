<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Bitrix\TransformerController\Entity\TimeStatisticTable;
use Bitrix\TransformerController\Entity\UsageStatisticTable;

/**
 * Statistic of time usage.
 *
 * This statistic is actual for each worker-machine separately.
 * Each machine knows only it is own statistic.
 */

class TimeStatistic
{
	const MODULE_ID = 'transformercontroller';

	const ERROR_CODE_WRONG_STATUS_BEFORE_DOWNLOAD = 100;
	const ERROR_CODE_WRONG_CONTENT_TYPE_BEFORE_DOWNLOAD = 101;
	const ERROR_CODE_FILE_IS_TOO_BIG_ON_DOWNLOAD = 102;
	const ERROR_CODE_DOMAIN_IS_BANNED = 103;
	const ERROR_CODE_QUEUE_ADD_EVENT = 150;
	const ERROR_CODE_QUEUE_ADD_FAIL = 151;
	const ERROR_CODE_QUEUE_NOT_FOUND = 152;
	const ERROR_CODE_MODULE_NOT_INSTALLED = 153;
	const ERROR_CODE_RIGHT_CHECK_FAILED = 154;
	const ERROR_CODE_LIMIT_EXCEEDED = 155;
	const ERROR_CODE_BACK_URL_HOST_MISMATCH = 156;
	const ERROR_CODE_DOMAIN_IS_PRIVATE = 157;
	const ERROR_CODE_WRONG_STATUS_AFTER_DOWNLOAD = 200;
	const ERROR_CODE_CANT_DOWNLOAD_FILE = 201;
	const ERROR_CODE_FILE_IS_TOO_BIG_AFTER_DOWNLOAD = 202;
	const ERROR_CODE_UPLOAD_FILES = 203;
	const ERROR_CODE_TRANSFORMATION_FAILED = 300;
	const ERROR_CODE_COMMAND_FAILED = 301;
	const ERROR_CODE_COMMAND_NOT_FOUND = 302;
	const ERROR_CODE_COMMAND_ERROR = 303;
	const ERROR_CODE_TRANSFORMATION_TIMED_OUT = 304;

	/**
	 * Add new record to statistic.
	 * @see DataManager::add()
	 *
	 * @param $data
	 * @return \Bitrix\Main\Entity\AddResult
	 * @throws \Exception
	 */
	public static function add($data)
	{
		return TimeStatisticTable::add($data);
	}

	/**
	 * Returns array with time statistic.
	 *
	 * @param DateTime $periodStart
	 * @param DateTime $periodEnd
	 * @param array $filter Filter for getList().
	 * @param array $group Group for getList().
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function get(DateTime $periodStart, DateTime $periodEnd, $filter = array(), $group = array('COMMAND_NAME'))
	{
		return TimeStatisticTable::query()
			->setSelect(array_merge($group, [
				new ExpressionField('MIN_WAIT_TIME', 'MIN(%s)', ['TIME_START']),
				new ExpressionField('AVG_WAIT_TIME', 'AVG(%s)', ['TIME_START']),
				new ExpressionField('MAX_WAIT_TIME', 'MAX(%s)', ['TIME_START']),
				new ExpressionField('MIN_EXEC_TIME', 'MIN(%s)', ['TIME_EXEC']),
				new ExpressionField('AVG_EXEC_TIME', 'AVG(%s)', ['TIME_EXEC']),
				new ExpressionField('MAX_EXEC_TIME', 'MAX(%s)', ['TIME_EXEC']),
				new ExpressionField('MIN_DOWNLOAD_TIME', 'MIN(%s)', ['TIME_DOWNLOAD']),
				new ExpressionField('AVG_DOWNLOAD_TIME', 'AVG(%s)', ['TIME_DOWNLOAD']),
				new ExpressionField('MAX_DOWNLOAD_TIME', 'MAX(%s)', ['TIME_DOWNLOAD']),
				new ExpressionField('MIN_UPLOAD_TIME', 'MIN(%s)', ['TIME_UPLOAD']),
				new ExpressionField('AVG_UPLOAD_TIME', 'AVG(%s)', ['TIME_UPLOAD']),
				new ExpressionField('MAX_UPLOAD_TIME', 'MAX(%s)', ['TIME_UPLOAD']),
				new ExpressionField('MIN_FULL_TIME', 'MIN(%s)', ['TIME_END']),
				new ExpressionField('AVG_FULL_TIME', 'AVG(%s)', ['TIME_END']),
				new ExpressionField('MAX_FULL_TIME', 'MAX(%s)', ['TIME_END']),
				new ExpressionField('TOTAL_FILE_SIZE', 'SUM(%s)', ['FILE_SIZE']),
				new ExpressionField('COUNT', 'COUNT(*)'),
			]))
			->setGroup($group)
			->setFilter(array_merge($filter, [
				'>TIME_END_ABSOLUTE' => $periodStart,
				'<TIME_END_ABSOLUTE' => $periodEnd,
			]))
			->exec()
			->fetchAll()
		;
	}

	public static function getErrorsCount(DateTime $periodStart, DateTime $periodEnd, $filter = array(), $group = array('COMMAND_NAME', 'ERROR'))
	{
		$errorData = Entity\TimeStatisticTable::getList(array(
			'select' => array_merge($group, array(
				new ExpressionField('ERRORS', 'COUNT(*)'),
			)),
			'filter' => array_merge($filter, array(
				'>TIME_END_ABSOLUTE' => $periodStart,
				'<TIME_END_ABSOLUTE' => $periodEnd,
				'>ERROR' => 0,
			)),
			'group' => $group,
		))->fetchAll();
		$result = array();
		foreach($errorData as $row)
		{
			$commandName = $row['COMMAND_NAME'] ?? null;
			$error = $row['ERROR'] ?? null;

			$result[$commandName][$error] = $row['ERRORS'] ?? null;
		}
		return $result;
	}

	public static function formatJson(array $data, array $errorData = array())
	{
		$result = array();
		foreach($data as $command)
		{
			$commandName = self::getJsonField($command['COMMAND_NAME'] ?? '');

			$result[$commandName] = [
				'wait_time' => [
					'min' => $command['MIN_WAIT_TIME'] ?? null,
					'avg' => $command['AVG_WAIT_TIME'] ?? null,
					'max' => $command['MAX_WAIT_TIME'] ?? null,
				],
				'download_time' => [
					'min' => $command['MIN_DOWNLOAD_TIME'] ?? null,
					'avg' => $command['AVG_DOWNLOAD_TIME'] ?? null,
					'max' => $command['MAX_DOWNLOAD_TIME'] ?? null,
				],
				'exec_time' => [
					'min' => $command['MIN_EXEC_TIME'] ?? null,
					'avg' => $command['AVG_EXEC_TIME'] ?? null,
					'max' => $command['MAX_EXEC_TIME'] ?? null,
				],
				'upload_time' => [
					'min' => $command['MIN_UPLOAD_TIME'] ?? null,
					'avg' => $command['AVG_UPLOAD_TIME'] ?? null,
					'max' => $command['MAX_UPLOAD_TIME'] ?? null,
				],
				'full_time' => [
					'min' => $command['MIN_FULL_TIME'] ?? null,
					'avg' => $command['AVG_FULL_TIME'] ?? null,
					'max' => $command['MAX_FULL_TIME'] ?? null,
				],
				'count' => $command['COUNT'] ?? null,
				'file_size' => $command['TOTAL_FILE_SIZE'] ?? null,
			];

			if(isset($errorData[$command['COMMAND_NAME']]))
			{
				$result[$commandName]['errors'] = $errorData[$command['COMMAND_NAME']];
			}
		}

		return $result;
	}

	public static function getMapFields()
	{
		return array(
			'Bitrix\\TransformerController\\Document' => 'document',
			'Bitrix\\TransformerController\\Video' => 'video',
			'Bitrix\TransformerController\Document' => 'document',
			'Bitrix\TransformerController\Video' => 'video',
		);
	}

	public static function getTimeDBColumnToJsonFieldMap(): array
	{
		return [
			'TIME_END' => 'full_time',
			'TIME_START' => 'wait_time',
			'TIME_DOWNLOAD' => 'download_time',
			'TIME_EXEC' => 'exec_time',
			'TIME_UPLOAD' => 'upload_time',
		];
	}

	/**
	 * @param string[] $jsonFields
	 *
	 * @return string[]
	 */
	public static function mapJsonFieldsToTimeDBColumns(array $jsonFields): array
	{
		$result = [];
		foreach (self::getTimeDBColumnToJsonFieldMap() as $dbColumnName => $jsonName)
		{
			if (in_array($jsonName, $jsonFields, true))
			{
				$result[] = $dbColumnName;
			}
		}

		return $result;
	}

	public static function mapTimeDBColumnToJsonField(string $dbColumnName): string
	{
		$map = self::getTimeDBColumnToJsonFieldMap();
		if (!isset($map[$dbColumnName]))
		{
			throw new ArgumentOutOfRangeException('dbColumnName', array_keys($map));
		}

		return $map[$dbColumnName];
	}

	public static function mapJsonFieldsToCommandNames(array $jsonFields): array
	{
		static $map = [
			/** @see self::getMapFields() - the same, but without duplicates with double slashes */
			'Bitrix\TransformerController\Document' => 'document',
			'Bitrix\TransformerController\Video' => 'video',
		];

		$result = [];
		foreach ($map as $commandName => $jsonField)
		{
			if (in_array($jsonField, $jsonFields, true))
			{
				$result[] = $commandName;
			}
		}

		return $result;
	}

	public static function getJsonField($field)
	{
		$mapFields = self::getMapFields();
		if(isset($mapFields[$field]))
		{
			return $mapFields[$field];
		}

		return $field;
	}

	public static function deleteOldAgent($days = 22, $portion = 500)
	{
		Entity\TimeStatisticTable::deleteOld($days, $portion);
		Entity\UsageStatisticTable::deleteOld($days, $portion);

		if (TimeStatisticTable::isThereOldRecords($days) || UsageStatisticTable::isThereOldRecords($days))
		{
			global $pPERIOD;

			// run this agent once again after 60 seconds
			$pPERIOD = 60;
		}

		return "\\Bitrix\\TransformerController\\TimeStatistic::deleteOldAgent({$days}, {$portion});";
	}
}
