<?php

namespace Bitrix\TransformerController\Entity;

use Bitrix\Main;

/**
 * Class BanListTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DOMAIN string mandatory
 * <li> LICENSE_KEY string mandatory
 * <li> DATE_ADD datetime mandatory
 * <li> DATE_END datetime optional
 * <li> REASON text optional
 * </ul>
 *
 * @package Bitrix\TransformerController
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BanList_Query query()
 * @method static EO_BanList_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BanList_Result getById($id)
 * @method static EO_BanList_Result getList(array $parameters = array())
 * @method static EO_BanList_Entity getEntity()
 * @method static \Bitrix\TransformerController\Entity\EO_BanList createObject($setDefaultValues = true)
 * @method static \Bitrix\TransformerController\Entity\EO_BanList_Collection createCollection()
 * @method static \Bitrix\TransformerController\Entity\EO_BanList wakeUpObject($row)
 * @method static \Bitrix\TransformerController\Entity\EO_BanList_Collection wakeUpCollection($rows)
 */

class BanListTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_transformercontroller_ban_list';
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
			new Main\Entity\StringField('DOMAIN', array('required' => true)),
			new Main\Entity\StringField('LICENSE_KEY'),
			new Main\Entity\DatetimeField('DATE_ADD'),
			new Main\Entity\DatetimeField('DATE_END'),
			new Main\Entity\TextField('REASON'),
			new Main\Entity\IntegerField('QUEUE_ID'),
			new Main\Entity\ReferenceField(
				'QUEUE',
				'Bitrix\TransformerController\Entity\QueueTable',
				array('=this.QUEUE_ID' => 'ref.ID')
			),
		);
	}
}