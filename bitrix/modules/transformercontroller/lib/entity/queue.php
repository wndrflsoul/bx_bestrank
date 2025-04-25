<?php

namespace Bitrix\TransformerController\Entity;

use Bitrix\Main;

/**
 * Class QueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Queue_Query query()
 * @method static EO_Queue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Queue_Result getById($id)
 * @method static EO_Queue_Result getList(array $parameters = array())
 * @method static EO_Queue_Entity getEntity()
 * @method static \Bitrix\TransformerController\Entity\EO_Queue createObject($setDefaultValues = true)
 * @method static \Bitrix\TransformerController\Entity\EO_Queue_Collection createCollection()
 * @method static \Bitrix\TransformerController\Entity\EO_Queue wakeUpObject($row)
 * @method static \Bitrix\TransformerController\Entity\EO_Queue_Collection wakeUpCollection($rows)
 */
class QueueTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_transformercontroller_queue';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Main\Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new Main\Entity\StringField('NAME', ['required' => true]),
			new Main\Entity\IntegerField('WORKERS', [
				'required' => true,
			]),
			new Main\Entity\IntegerField('SORT', [
				'required' => true,
				'default_value' => 500,
			]),
		];
	}
}