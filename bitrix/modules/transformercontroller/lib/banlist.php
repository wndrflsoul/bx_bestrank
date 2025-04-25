<?

namespace Bitrix\TransformerController;

use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\TransformerController\Entity\BanListTable;

/**
 * List of banned portals.
 */

class BanList
{
	/**
	 * Returns active row by domain.
	 *
	 * @param string $domain
	 * @param string $queueName
	 * @throws \Exception
	 * @return array|false
	 */
	public static function getByDomain($domain, $queueName = '')
	{
		if(empty($domain))
		{
			return false;
		}

		$filter = [
			'=DOMAIN' => $domain,
			[
				'LOGIC' => 'OR',
				['DATE_END' => DateTime::createFromTimestamp(0)],
				['>DATE_END' => new DateTime()],
			]
		];

		if(!empty($queueName))
		{
			$filter['=QUEUE.NAME'] = $queueName;
		}

		return BanListTable::getList([
			'filter' => $filter
		])->fetch();
	}

	/**
	 * Return list of all active ban portals.
	 * If $all is true returns bans for all the time.
	 *
	 * @param bool $all
	 * @return array
	 */
	public static function getList($all = false)
	{
		if($all)
		{
			return BanListTable::getList()->fetchAll();
		}
		else
		{
			return BanListTable::getList(array('filter' => array(
				'LOGIC' => 'OR',
				array('DATE_END' => DateTime::createFromTimestamp(0)),
				array('>DATE_END' => new DateTime())
			)))->fetchAll();
		}
	}

	/**
	 * Add ban.
	 * If ban for particular domain is exists - row will be updated, we do not store multiple bans.
	 *
	 * @param $data
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult
	 */
	public static function add($data)
	{
		if(empty($data['DATE_ADD']))
		{
			$data['DATE_ADD'] = new DateTime();
		}
		$ban = BanListTable::getList(array(
			'filter' => array(
				'=DOMAIN' => $data['DOMAIN']
			)
		))->fetch();
		if($ban)
		{
			return BanListTable::update($ban['ID'], $data);
		}

		return BanListTable::add($data);
	}

	/**
	 * Delete row by domain.
	 *
	 * @param $domain
	 * @param string $queueName
	 * @return DeleteResult
	 * @throws \Exception
	 */
	public static function delete($domain, $queueName = '')
	{
		$result = new DeleteResult();
		if(empty($domain))
		{
			$result->addError(new Error('DOMAIN is empty'));
			return $result;
		}
		$ban = self::getByDomain($domain, $queueName);
		if($ban)
		{
			return BanListTable::delete($ban['ID']);
		}
		return $result;
	}
}