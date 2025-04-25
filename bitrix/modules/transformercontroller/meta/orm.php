<?php

/* ORMENTITYANNOTATION:Bitrix\TransformerController\Entity\BanListTable:transformercontroller/lib/entity/banlist.php:453877b119dbd0c8419d0c9f7536671d */
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_BanList
	 * @see \Bitrix\TransformerController\Entity\BanListTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setLicenseKey(\string|\Bitrix\Main\DB\SqlExpression $licenseKey)
	 * @method bool hasLicenseKey()
	 * @method bool isLicenseKeyFilled()
	 * @method bool isLicenseKeyChanged()
	 * @method \string remindActualLicenseKey()
	 * @method \string requireLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetLicenseKey()
	 * @method \string fillLicenseKey()
	 * @method \Bitrix\Main\Type\DateTime getDateAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setDateAdd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateAdd)
	 * @method bool hasDateAdd()
	 * @method bool isDateAddFilled()
	 * @method bool isDateAddChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateAdd()
	 * @method \Bitrix\Main\Type\DateTime requireDateAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetDateAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetDateAdd()
	 * @method \Bitrix\Main\Type\DateTime fillDateAdd()
	 * @method \Bitrix\Main\Type\DateTime getDateEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setDateEnd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateEnd)
	 * @method bool hasDateEnd()
	 * @method bool isDateEndFilled()
	 * @method bool isDateEndChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateEnd()
	 * @method \Bitrix\Main\Type\DateTime requireDateEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetDateEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetDateEnd()
	 * @method \Bitrix\Main\Type\DateTime fillDateEnd()
	 * @method \string getReason()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setReason(\string|\Bitrix\Main\DB\SqlExpression $reason)
	 * @method bool hasReason()
	 * @method bool isReasonFilled()
	 * @method bool isReasonChanged()
	 * @method \string remindActualReason()
	 * @method \string requireReason()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetReason()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetReason()
	 * @method \string fillReason()
	 * @method \int getQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue getQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue remindActualQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue requireQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList setQueue(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList resetQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unsetQueue()
	 * @method bool hasQueue()
	 * @method bool isQueueFilled()
	 * @method bool isQueueChanged()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList set($fieldName, $value)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList reset($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\TransformerController\Entity\EO_BanList wakeUp($data)
	 */
	class EO_BanList {
		/* @var \Bitrix\TransformerController\Entity\BanListTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\BanListTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_BanList_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getLicenseKeyList()
	 * @method \string[] fillLicenseKey()
	 * @method \Bitrix\Main\Type\DateTime[] getDateAddList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateAdd()
	 * @method \Bitrix\Main\Type\DateTime[] getDateEndList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateEnd()
	 * @method \string[] getReasonList()
	 * @method \string[] fillReason()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue[] getQueueList()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList_Collection getQueueCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\TransformerController\Entity\EO_BanList $object)
	 * @method bool has(\Bitrix\TransformerController\Entity\EO_BanList $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList getByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList[] getAll()
	 * @method bool remove(\Bitrix\TransformerController\Entity\EO_BanList $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\TransformerController\Entity\EO_BanList_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\TransformerController\Entity\EO_BanList current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_BanList_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\TransformerController\Entity\BanListTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\BanListTable';
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_BanList_Result exec()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_BanList_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_BanList fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList_Collection fetchCollection()
	 */
	class EO_BanList_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_BanList createObject($setDefaultValues = true)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList_Collection createCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_BanList wakeUpObject($row)
	 * @method \Bitrix\TransformerController\Entity\EO_BanList_Collection wakeUpCollection($rows)
	 */
	class EO_BanList_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\TransformerController\Entity\LimitsTable:transformercontroller/lib/entity/limits.php:e01cc52c3649b4c994d14f6931b12816 */
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_Limits
	 * @see \Bitrix\TransformerController\Entity\LimitsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setTarif(\string|\Bitrix\Main\DB\SqlExpression $tarif)
	 * @method bool hasTarif()
	 * @method bool isTarifFilled()
	 * @method bool isTarifChanged()
	 * @method \string remindActualTarif()
	 * @method \string requireTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetTarif()
	 * @method \string fillTarif()
	 * @method \string getType()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetType()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetType()
	 * @method \string fillType()
	 * @method \string getCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setCommandName(\string|\Bitrix\Main\DB\SqlExpression $commandName)
	 * @method bool hasCommandName()
	 * @method bool isCommandNameFilled()
	 * @method bool isCommandNameChanged()
	 * @method \string remindActualCommandName()
	 * @method \string requireCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetCommandName()
	 * @method \string fillCommandName()
	 * @method \string getDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setLicenseKey(\string|\Bitrix\Main\DB\SqlExpression $licenseKey)
	 * @method bool hasLicenseKey()
	 * @method bool isLicenseKeyFilled()
	 * @method bool isLicenseKeyChanged()
	 * @method \string remindActualLicenseKey()
	 * @method \string requireLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetLicenseKey()
	 * @method \string fillLicenseKey()
	 * @method \int getCommandsCount()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setCommandsCount(\int|\Bitrix\Main\DB\SqlExpression $commandsCount)
	 * @method bool hasCommandsCount()
	 * @method bool isCommandsCountFilled()
	 * @method bool isCommandsCountChanged()
	 * @method \int remindActualCommandsCount()
	 * @method \int requireCommandsCount()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetCommandsCount()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetCommandsCount()
	 * @method \int fillCommandsCount()
	 * @method \int getFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \int getPeriod()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setPeriod(\int|\Bitrix\Main\DB\SqlExpression $period)
	 * @method bool hasPeriod()
	 * @method bool isPeriodFilled()
	 * @method bool isPeriodChanged()
	 * @method \int remindActualPeriod()
	 * @method \int requirePeriod()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetPeriod()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetPeriod()
	 * @method \int fillPeriod()
	 * @method \int getQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue getQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue remindActualQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue requireQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits setQueue(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits resetQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unsetQueue()
	 * @method bool hasQueue()
	 * @method bool isQueueFilled()
	 * @method bool isQueueChanged()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits set($fieldName, $value)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits reset($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\TransformerController\Entity\EO_Limits wakeUp($data)
	 */
	class EO_Limits {
		/* @var \Bitrix\TransformerController\Entity\LimitsTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\LimitsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_Limits_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTarifList()
	 * @method \string[] fillTarif()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getCommandNameList()
	 * @method \string[] fillCommandName()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getLicenseKeyList()
	 * @method \string[] fillLicenseKey()
	 * @method \int[] getCommandsCountList()
	 * @method \int[] fillCommandsCount()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \int[] getPeriodList()
	 * @method \int[] fillPeriod()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue[] getQueueList()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits_Collection getQueueCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\TransformerController\Entity\EO_Limits $object)
	 * @method bool has(\Bitrix\TransformerController\Entity\EO_Limits $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits getByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits[] getAll()
	 * @method bool remove(\Bitrix\TransformerController\Entity\EO_Limits $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\TransformerController\Entity\EO_Limits_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\TransformerController\Entity\EO_Limits current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Limits_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\TransformerController\Entity\LimitsTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\LimitsTable';
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Limits_Result exec()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Limits_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_Limits fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits_Collection fetchCollection()
	 */
	class EO_Limits_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_Limits createObject($setDefaultValues = true)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits_Collection createCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Limits wakeUpObject($row)
	 * @method \Bitrix\TransformerController\Entity\EO_Limits_Collection wakeUpCollection($rows)
	 */
	class EO_Limits_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\TransformerController\Entity\QueueTable:transformercontroller/lib/entity/queue.php:5523be432ceefde0addfb8c25a197576 */
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_Queue
	 * @see \Bitrix\TransformerController\Entity\QueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue resetName()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue unsetName()
	 * @method \string fillName()
	 * @method \int getWorkers()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue setWorkers(\int|\Bitrix\Main\DB\SqlExpression $workers)
	 * @method bool hasWorkers()
	 * @method bool isWorkersFilled()
	 * @method bool isWorkersChanged()
	 * @method \int remindActualWorkers()
	 * @method \int requireWorkers()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue resetWorkers()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue unsetWorkers()
	 * @method \int fillWorkers()
	 * @method \int getSort()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue resetSort()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue unsetSort()
	 * @method \int fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue set($fieldName, $value)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue reset($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\TransformerController\Entity\EO_Queue wakeUp($data)
	 */
	class EO_Queue {
		/* @var \Bitrix\TransformerController\Entity\QueueTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\QueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_Queue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getWorkersList()
	 * @method \int[] fillWorkers()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method bool has(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue getByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue[] getAll()
	 * @method bool remove(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\TransformerController\Entity\EO_Queue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\TransformerController\Entity\EO_Queue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Queue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\TransformerController\Entity\QueueTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\QueueTable';
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Queue_Result exec()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Queue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fetchCollection()
	 */
	class EO_Queue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_Queue createObject($setDefaultValues = true)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection createCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue wakeUpObject($row)
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection wakeUpCollection($rows)
	 */
	class EO_Queue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\TransformerController\Entity\TimeStatisticTable:transformercontroller/lib/entity/timestatistic.php:a68ddce16f1a2edf3758475add175cd9 */
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_TimeStatistic
	 * @see \Bitrix\TransformerController\Entity\TimeStatisticTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setCommandName(\string|\Bitrix\Main\DB\SqlExpression $commandName)
	 * @method bool hasCommandName()
	 * @method bool isCommandNameFilled()
	 * @method bool isCommandNameChanged()
	 * @method \string remindActualCommandName()
	 * @method \string requireCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetCommandName()
	 * @method \string fillCommandName()
	 * @method \int getFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setLicenseKey(\string|\Bitrix\Main\DB\SqlExpression $licenseKey)
	 * @method bool hasLicenseKey()
	 * @method bool isLicenseKeyFilled()
	 * @method bool isLicenseKeyChanged()
	 * @method \string remindActualLicenseKey()
	 * @method \string requireLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetLicenseKey()
	 * @method \string fillLicenseKey()
	 * @method \int getError()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setError(\int|\Bitrix\Main\DB\SqlExpression $error)
	 * @method bool hasError()
	 * @method bool isErrorFilled()
	 * @method bool isErrorChanged()
	 * @method \int remindActualError()
	 * @method \int requireError()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetError()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetError()
	 * @method \int fillError()
	 * @method \string getErrorInfo()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setErrorInfo(\string|\Bitrix\Main\DB\SqlExpression $errorInfo)
	 * @method bool hasErrorInfo()
	 * @method bool isErrorInfoFilled()
	 * @method bool isErrorInfoChanged()
	 * @method \string remindActualErrorInfo()
	 * @method \string requireErrorInfo()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetErrorInfo()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetErrorInfo()
	 * @method \string fillErrorInfo()
	 * @method \Bitrix\Main\Type\DateTime getTimeAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeAdd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timeAdd)
	 * @method bool hasTimeAdd()
	 * @method bool isTimeAddFilled()
	 * @method bool isTimeAddChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimeAdd()
	 * @method \Bitrix\Main\Type\DateTime requireTimeAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeAdd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeAdd()
	 * @method \Bitrix\Main\Type\DateTime fillTimeAdd()
	 * @method \int getTimeStart()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeStart(\int|\Bitrix\Main\DB\SqlExpression $timeStart)
	 * @method bool hasTimeStart()
	 * @method bool isTimeStartFilled()
	 * @method bool isTimeStartChanged()
	 * @method \int remindActualTimeStart()
	 * @method \int requireTimeStart()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeStart()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeStart()
	 * @method \int fillTimeStart()
	 * @method \int getTimeExec()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeExec(\int|\Bitrix\Main\DB\SqlExpression $timeExec)
	 * @method bool hasTimeExec()
	 * @method bool isTimeExecFilled()
	 * @method bool isTimeExecChanged()
	 * @method \int remindActualTimeExec()
	 * @method \int requireTimeExec()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeExec()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeExec()
	 * @method \int fillTimeExec()
	 * @method \int getTimeUpload()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeUpload(\int|\Bitrix\Main\DB\SqlExpression $timeUpload)
	 * @method bool hasTimeUpload()
	 * @method bool isTimeUploadFilled()
	 * @method bool isTimeUploadChanged()
	 * @method \int remindActualTimeUpload()
	 * @method \int requireTimeUpload()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeUpload()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeUpload()
	 * @method \int fillTimeUpload()
	 * @method \int getTimeEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeEnd(\int|\Bitrix\Main\DB\SqlExpression $timeEnd)
	 * @method bool hasTimeEnd()
	 * @method bool isTimeEndFilled()
	 * @method bool isTimeEndChanged()
	 * @method \int remindActualTimeEnd()
	 * @method \int requireTimeEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeEnd()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeEnd()
	 * @method \int fillTimeEnd()
	 * @method \Bitrix\Main\Type\DateTime getTimeEndAbsolute()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setTimeEndAbsolute(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timeEndAbsolute)
	 * @method bool hasTimeEndAbsolute()
	 * @method bool isTimeEndAbsoluteFilled()
	 * @method bool isTimeEndAbsoluteChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimeEndAbsolute()
	 * @method \Bitrix\Main\Type\DateTime requireTimeEndAbsolute()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetTimeEndAbsolute()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetTimeEndAbsolute()
	 * @method \Bitrix\Main\Type\DateTime fillTimeEndAbsolute()
	 * @method \int getQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \string getGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setGuid(\string|\Bitrix\Main\DB\SqlExpression $guid)
	 * @method bool hasGuid()
	 * @method bool isGuidFilled()
	 * @method bool isGuidChanged()
	 * @method \string remindActualGuid()
	 * @method \string requireGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetGuid()
	 * @method \string fillGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue getQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue remindActualQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue requireQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic setQueue(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic resetQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unsetQueue()
	 * @method bool hasQueue()
	 * @method bool isQueueFilled()
	 * @method bool isQueueChanged()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic set($fieldName, $value)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic reset($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\TransformerController\Entity\EO_TimeStatistic wakeUp($data)
	 */
	class EO_TimeStatistic {
		/* @var \Bitrix\TransformerController\Entity\TimeStatisticTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\TimeStatisticTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_TimeStatistic_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCommandNameList()
	 * @method \string[] fillCommandName()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getLicenseKeyList()
	 * @method \string[] fillLicenseKey()
	 * @method \int[] getErrorList()
	 * @method \int[] fillError()
	 * @method \string[] getErrorInfoList()
	 * @method \string[] fillErrorInfo()
	 * @method \Bitrix\Main\Type\DateTime[] getTimeAddList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimeAdd()
	 * @method \int[] getTimeStartList()
	 * @method \int[] fillTimeStart()
	 * @method \int[] getTimeExecList()
	 * @method \int[] fillTimeExec()
	 * @method \int[] getTimeUploadList()
	 * @method \int[] fillTimeUpload()
	 * @method \int[] getTimeEndList()
	 * @method \int[] fillTimeEnd()
	 * @method \Bitrix\Main\Type\DateTime[] getTimeEndAbsoluteList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimeEndAbsolute()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \string[] getGuidList()
	 * @method \string[] fillGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue[] getQueueList()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection getQueueCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\TransformerController\Entity\EO_TimeStatistic $object)
	 * @method bool has(\Bitrix\TransformerController\Entity\EO_TimeStatistic $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic getByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic[] getAll()
	 * @method bool remove(\Bitrix\TransformerController\Entity\EO_TimeStatistic $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TimeStatistic_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\TransformerController\Entity\TimeStatisticTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\TimeStatisticTable';
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TimeStatistic_Result exec()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TimeStatistic_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection fetchCollection()
	 */
	class EO_TimeStatistic_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic createObject($setDefaultValues = true)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection createCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic wakeUpObject($row)
	 * @method \Bitrix\TransformerController\Entity\EO_TimeStatistic_Collection wakeUpCollection($rows)
	 */
	class EO_TimeStatistic_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\TransformerController\Entity\UsageStatisticTable:transformercontroller/lib/entity/usagestatistic.php:39a2229ebb442f45572dff4b6ad71b1b */
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_UsageStatistic
	 * @see \Bitrix\TransformerController\Entity\UsageStatisticTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setCommandName(\string|\Bitrix\Main\DB\SqlExpression $commandName)
	 * @method bool hasCommandName()
	 * @method bool isCommandNameFilled()
	 * @method bool isCommandNameChanged()
	 * @method \string remindActualCommandName()
	 * @method \string requireCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetCommandName()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetCommandName()
	 * @method \string fillCommandName()
	 * @method \int getFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetFileSize()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetDomain()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setLicenseKey(\string|\Bitrix\Main\DB\SqlExpression $licenseKey)
	 * @method bool hasLicenseKey()
	 * @method bool isLicenseKeyFilled()
	 * @method bool isLicenseKeyChanged()
	 * @method \string remindActualLicenseKey()
	 * @method \string requireLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetLicenseKey()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetLicenseKey()
	 * @method \string fillLicenseKey()
	 * @method \string getTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setTarif(\string|\Bitrix\Main\DB\SqlExpression $tarif)
	 * @method bool hasTarif()
	 * @method bool isTarifFilled()
	 * @method bool isTarifChanged()
	 * @method \string remindActualTarif()
	 * @method \string requireTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetTarif()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetTarif()
	 * @method \string fillTarif()
	 * @method \Bitrix\Main\Type\DateTime getDate()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $date)
	 * @method bool hasDate()
	 * @method bool isDateFilled()
	 * @method bool isDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDate()
	 * @method \Bitrix\Main\Type\DateTime requireDate()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetDate()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetDate()
	 * @method \Bitrix\Main\Type\DateTime fillDate()
	 * @method \int getQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetQueueId()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \string getGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setGuid(\string|\Bitrix\Main\DB\SqlExpression $guid)
	 * @method bool hasGuid()
	 * @method bool isGuidFilled()
	 * @method bool isGuidChanged()
	 * @method \string remindActualGuid()
	 * @method \string requireGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetGuid()
	 * @method \string fillGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue getQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue remindActualQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue requireQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic setQueue(\Bitrix\TransformerController\Entity\EO_Queue $object)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic resetQueue()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unsetQueue()
	 * @method bool hasQueue()
	 * @method bool isQueueFilled()
	 * @method bool isQueueChanged()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic set($fieldName, $value)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic reset($fieldName)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic wakeUp($data)
	 */
	class EO_UsageStatistic {
		/* @var \Bitrix\TransformerController\Entity\UsageStatisticTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\UsageStatisticTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * EO_UsageStatistic_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCommandNameList()
	 * @method \string[] fillCommandName()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getLicenseKeyList()
	 * @method \string[] fillLicenseKey()
	 * @method \string[] getTarifList()
	 * @method \string[] fillTarif()
	 * @method \Bitrix\Main\Type\DateTime[] getDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDate()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \string[] getGuidList()
	 * @method \string[] fillGuid()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue[] getQueueList()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection getQueueCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_Queue_Collection fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\TransformerController\Entity\EO_UsageStatistic $object)
	 * @method bool has(\Bitrix\TransformerController\Entity\EO_UsageStatistic $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic getByPrimary($primary)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic[] getAll()
	 * @method bool remove(\Bitrix\TransformerController\Entity\EO_UsageStatistic $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UsageStatistic_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\TransformerController\Entity\UsageStatisticTable */
		static public $dataClass = '\Bitrix\TransformerController\Entity\UsageStatisticTable';
	}
}
namespace Bitrix\TransformerController\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UsageStatistic_Result exec()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UsageStatistic_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic fetchObject()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection fetchCollection()
	 */
	class EO_UsageStatistic_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic createObject($setDefaultValues = true)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection createCollection()
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic wakeUpObject($row)
	 * @method \Bitrix\TransformerController\Entity\EO_UsageStatistic_Collection wakeUpCollection($rows)
	 */
	class EO_UsageStatistic_Entity extends \Bitrix\Main\ORM\Entity {}
}