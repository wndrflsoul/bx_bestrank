<?php
IncludeModuleLangFile(__FILE__);

class CClusterQueue
{
	public static function Add($group_id, $command, $param1, $param2, $param3)
	{
		global $DB;

		$sql_group_id = "'" . intval($group_id) . "'";
		$sql_command = "'" . $DB->ForSql($command, 50) . "'";
		$sql_param1 = CClusterQueue::QuoteParam($param1);
		$sql_param2 = CClusterQueue::QuoteParam($param2);
		$sql_param3 = CClusterQueue::QuoteParam($param3);
		$DB->StartUsingMasterOnly();
		$DB->Query('
			INSERT INTO b_cluster_queue (
			TIMESTAMP_X, GROUP_ID, COMMAND, PARAM1, PARAM2, PARAM3
			) values (
			' . $DB->CurrentTimeFunction() . ', ' . $sql_group_id . ', ' . $sql_command . ', ' . $sql_param1 . ', ' . $sql_param2 . ', ' . $sql_param3 . '
			)
		');
		$DB->StopUsingMasterOnly();
	}

	public static function QuoteParam($str)
	{
		global $DB;

		if (is_bool($str))
		{
			return "'b:" . ($str === true ? 't' : 'f') . "'";
		}
		elseif (is_string($str))
		{
			return "'s:" . $DB->ForSql($str, 250) . "'";
		}
		else
		{
			return 'null';
		}
	}

	public static function UnQuoteParam($str)
	{
		if ($str <> '')
		{
			$prefix = mb_substr($str, 0, 2);
			if ($prefix === 's:')
			{
				return mb_substr($str, 2);
			}
			if ($prefix === 'b:')
			{
				return mb_substr($str, 2) === 't';
			}
		}
		return null;
	}

	public static function Run()
	{
		$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
		/* @var \Bitrix\Main\DB\Connection $connection */
		$connection = $pool->getConnection();
		$pool->useMasterOnly(true);
		do
		{
			//read data
			$ids = [];
			$queue = [];
			$rs = $connection->query('
				SELECT *
				FROM b_cluster_queue
				WHERE GROUP_ID = ' . constant('BX_CLUSTER_GROUP') . '
				ORDER BY ID
			', 100);
			while ($ar = $rs->fetch())
			{
				$queueKey = $ar['COMMAND'] . '|' . $ar['PARAM1'] . '|' . $ar['PARAM2'] . '|' . $ar['PARAM3'];
				$queue[$queueKey] = $ar;
				$ids[] = intval($ar['ID']);
			}

			$uid = $connection->getDatabase() . '_cluster_queue_' . constant('BX_CLUSTER_GROUP');

			if ($ids)
			{
				if (!$connection->lock($uid))
				{
					$pool->useMasterOnly(false);
					return false;
				}
			}

			//clean cache
			foreach ($queue as $ar)
			{
				$class_name = $ar['COMMAND'];
				if (class_exists($class_name))
				{
					/**  @var CPHPCacheRedisCluster::QueueRun | CPHPCacheMemcacheCluster::QueueRun $class_name */
					$object = new $class_name;
					$object->QueueRun(
						CClusterQueue::UnQuoteParam($ar['PARAM1']),
						CClusterQueue::UnQuoteParam($ar['PARAM2']),
						CClusterQueue::UnQuoteParam($ar['PARAM3'])
					);
				}
			}

			//mark as done
			if ($ids)
			{
				$connection->query('DELETE FROM b_cluster_queue WHERE ID in (' . implode(',', $ids) . ')');
				$connection->unlock($uid);
			}
		}
		while ($queue);
		$pool->useMasterOnly(false);
	}
}
