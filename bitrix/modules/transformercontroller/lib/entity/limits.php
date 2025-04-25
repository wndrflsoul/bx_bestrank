<?php

namespace Bitrix\TransformerController\Entity;

use Bitrix\Main;

/**
 * Class LimitsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TARIF string optional
 * <li> COMMAND_NAME string optional
 * <li> DOMAIN string optional
 * <li> LICENSE_KEY string optional
 * <li> COMMANDS_COUNT int optional
 * <li> FILE_SIZE int optional
 * <li> PERIOD int optional
 * </ul>
 *
 * @package Bitrix\TransformerController
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Limits_Query query()
 * @method static EO_Limits_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Limits_Result getById($id)
 * @method static EO_Limits_Result getList(array $parameters = array())
 * @method static EO_Limits_Entity getEntity()
 * @method static \Bitrix\TransformerController\Entity\EO_Limits createObject($setDefaultValues = true)
 * @method static \Bitrix\TransformerController\Entity\EO_Limits_Collection createCollection()
 * @method static \Bitrix\TransformerController\Entity\EO_Limits wakeUpObject($row)
 * @method static \Bitrix\TransformerController\Entity\EO_Limits_Collection wakeUpCollection($rows)
 */

class LimitsTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_transformercontroller_limits';
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
			new Main\Entity\StringField('TARIF'),
			new Main\Entity\StringField('TYPE'),
			new Main\Entity\StringField('COMMAND_NAME'),
			new Main\Entity\StringField('DOMAIN'),
			new Main\Entity\StringField('LICENSE_KEY'),
			new Main\Entity\IntegerField('COMMANDS_COUNT'),
			new Main\Entity\IntegerField('FILE_SIZE'),
			new Main\Entity\IntegerField('PERIOD'),
			new Main\Entity\IntegerField('QUEUE_ID'),
			new Main\Entity\ReferenceField(
				'QUEUE',
				'Bitrix\TransformerController\Entity\QueueTable',
				array('=this.QUEUE_ID' => 'ref.ID')
			),
		);
	}

	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		return self::checkLimits($event);
	}

	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		return self::checkLimits($event);
	}

	private static function checkLimits(Main\Entity\Event $event)
	{
		$data = $event->getParameter("fields");
		$result = new Main\Entity\EventResult();

		if(empty($data['COMMANDS_COUNT']) && empty($data['FILE_SIZE']))
		{
			$result->addError(new Main\Entity\EntityError('Field COMMANDS_COUNT or FILE_SIZE should be filled'));
		}

		return $result;
	}
}