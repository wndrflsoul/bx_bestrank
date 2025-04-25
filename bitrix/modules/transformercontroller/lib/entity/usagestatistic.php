<?php

namespace Bitrix\TransformerController\Entity;

use Bitrix\Main;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\Date;

/**
 * Class UsageStatisticTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> COMMAND_NAME string mandatory
 * <li> FILE_SIZE int optional
 * <li> DOMAIN string mandatory
 * <li> LICENSE_KEY string optional
 * <li> TARIF string optional
 * <li> DATE datetime optional
 * </ul>
 *
 * @package Bitrix\TransformerController
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UsageStatistic_Query query()
 * @method static EO_UsageStatistic_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_UsageStatistic_Result getById($id)
 * @method static EO_UsageStatistic_Result getList(array $parameters = array())
 * @method static EO_UsageStatistic_Entity getEntity()
 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic createObject($setDefaultValues = true)
 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection createCollection()
 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic wakeUpObject($row)
 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection wakeUpCollection($rows)
 */

class UsageStatisticTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_transformercontroller_usage_statistic';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new Main\Entity\StringField('COMMAND_NAME', array('required' => true)),
			new Main\Entity\IntegerField('FILE_SIZE'),
			new Main\Entity\StringField('DOMAIN', array('required' => true)),
			new Main\Entity\StringField('LICENSE_KEY'),
			new Main\Entity\StringField('TARIF'),
			new Main\Entity\DatetimeField('DATE', array('default_value' => static function() {
				return new Main\Type\DateTime();
			})),
			new Main\Entity\IntegerField('QUEUE_ID'),
			(new Main\Entity\StringField('GUID'))
				->configureNullable()
				->configureUnique()
			,
			new Main\Entity\ReferenceField(
				'QUEUE',
				'Bitrix\TransformerController\Entity\QueueTable',
				array('=this.QUEUE_ID' => 'ref.ID')
			),
		);
	}

	/**
	 * @param int $days Records older then $days will be cleaned
	 * @param int $portion Number of records to clean at once
	 * @return void
	 */
	public static function deleteOld(int $days = 22, $portion = 500): void
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$sql = $connection->getSqlHelper()->prepareDeleteLimit(
			self::getTableName(),
			['ID'],
			Query::buildFilterSql($entity, self::getOldRecordsFilter($days)),
			['DATE' => 'ASC'],
			(int)$portion,
		);

		$connection->query($sql);

		self::cleanCache();
	}

	private static function getOldRecordsFilter(int $days): Main\ORM\Query\Filter\ConditionTree
	{
		$cleanTime = new Date();
		$cleanTime->add("-{$days} day");

		return self::query()::filter()
			->logic('or')
			->whereNull('DATE')
			->where('DATE', '<', $cleanTime);
	}

	/**
	 * @internal
	 */
	public static function isThereOldRecords(int $days = 22): bool
	{
		return (bool)self::query()
			->setSelect(['ID'])
			->where(
				self::getOldRecordsFilter($days)
			)
			->setLimit(1)
			->fetch()
		;
	}
}
