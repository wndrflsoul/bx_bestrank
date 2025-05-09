<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CUser $USER */
global $USER;
/** @global CDatabase $DB */
global $DB;

if (!$USER->isAdmin() || !check_bitrix_sessid())
{
	echo GetMessage('CLUWIZ_ERROR_ACCESS_DENIED');
	require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
	die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/wizard.php';

$lang = $_REQUEST['lang'];
if (!preg_match('/^[a-z0-9_]{2}$/i', $lang))
{
	$lang = 'en';
}

$wizard = new CWizard('bitrix:cluster.slave_start');
$wizard->IncludeWizardLang('scripts/move.php', $lang);

CModule::IncludeModule('cluster');

$STEP = intval($_REQUEST['STEP']);

$node_id = intval($_REQUEST['node_id']);
if ($node_id < 2)
{
	$nodeDB = false;
}
else
{
	$nodeDB = CDatabase::GetDBNodeConnection($node_id, true, false);
}

if (!is_object($nodeDB))
{
	echo GetMessage('CLUWIZ_CONNECTION_ERROR');
}
elseif ($STEP < 2)
{
	$DB->Query('DELETE FROM b_cluster_table', false, '', ['fixed_connection' => true]);
	$strError = CreateNodeTable($DB, $nodeDB, 'b_cluster_table');

	$rsTables = $DB->Query('show tables', false, '', ['fixed_connection' => true]);
	while ($arTable = $rsTables->Fetch())
	{
		$table_name = $arTable['Tables_in_' . $DB->DBName];

		if ($table_name == 'b_cluster_table')
		{
			continue;
		}

		$key_column = false;
		$rsIndexes = $DB->Query('SHOW INDEX FROM `' . $DB->ForSql($table_name) . '`', true, '', ['fixed_connection' => true]);
		if ($rsIndexes)
		{
			$arIndexes = [];
			while ($ar = $rsIndexes->Fetch())
			{
				if ($ar['Non_unique'] == '0')
				{
					$key_name = $ar['Key_name'];
					if (!isset($arIndexes[$key_name]))
					{
						$arIndexes[$key_name] = [];
					}
					$arIndexes[$key_name][$ar['Seq_in_index']] = $ar['Column_name'];
				}
			}

			foreach ($arIndexes as $IndexName => $arIndexColumns)
			{
				if (count($arIndexColumns) == 1)
				{
					$key_column = current($arIndexColumns);
					break;
				}
			}
		}

		$nodeDB->Add('b_cluster_table', [
			'MODULE_ID' => 'main',
			'TABLE_NAME' => $table_name,
			'KEY_COLUMN' => $key_column,
			'FROM_NODE_ID' => 1,
			'TO_NODE_ID' => $node_id,
			'LAST_ID' => false,
		]);
	}

	echo GetMessage('CLUWIZ_INIT');
	echo '<script>BX.Cluster.SlaveStart.MoveTables(2)</script>';
}
else
{
	$DB->Query('FLUSH TABLES WITH READ LOCK', false, '', ['fixed_connection' => true]);
	$nodeDB->Query('SET FOREIGN_KEY_CHECKS=0', false, '', ['fixed_connection' => true]);

	$strError = '';
	$end_time = time() + 5;
	do
	{
		$rsTables = $nodeDB->Query('SELECT * FROM b_cluster_table ORDER BY ID LIMIT 1', false, '', ['fixed_connection' => true]);
		$arTable = $rsTables->Fetch();
		if (!is_array($arTable))
		{
			break;
		}

		if ($arTable['LAST_ID'] == '')
		{
			$strError = CreateNodeTable($DB, $nodeDB, $arTable['TABLE_NAME']);
		}

		if ($strError)
		{
			echo $strError;
			break;
		}

			//It is a view
		if ($strError === false)
		{
			$nodeDB->Query("
				DELETE FROM b_cluster_table
				WHERE ID = '" . $arTable['ID'] . "'
			", false, '', ['fixed_connection' => true]);
			continue;
		}

		try
		{
			$arTable['COLUMNS'] = GetTableColumns($DB, $arTable['TABLE_NAME']);
		}
		catch (\Bitrix\Main\DB\SqlQueryException $e)
		{
			$strError = $e->getMessage();
		}

		if ($strError)
		{
			echo $strError;
			break;
		}

		$i = intval($arTable['REC_COUNT']);
		$di = 0;
		$last_id = '';
		$strInsert = '';
		if ($arTable['KEY_COLUMN'] <> '')
		{
			$strSelect = '
				SELECT *
				FROM ' . $arTable['TABLE_NAME'] . '
				' . ($arTable['LAST_ID'] <> '' ? 'WHERE ' . $arTable['KEY_COLUMN'] . " > '" . $arTable['LAST_ID'] . "'" : '') . '
				ORDER BY ' . $arTable['KEY_COLUMN'] . '
				LIMIT 1000
			';
		}
		else
		{
			$strSelect = '
				SELECT *
				FROM ' . $arTable['TABLE_NAME'] . '
				LIMIT ' . ($arTable['LAST_ID'] <> '' ? $arTable['LAST_ID'] . ', ' : '') . '1000
			';
		}
		$rsSource = $DB->Query($strSelect, false, '', ['fixed_connection' => true]);
		while ($arSource = $rsSource->Fetch())
		{
			$i++;
			$di++;

			if (!$strInsert)
			{
				$strInsert = 'insert into ' . $arTable['TABLE_NAME'] . ' values';
			}
			else
			{
				$strInsert .= ',';
			}

			foreach ($arSource as $key => $value)
			{
				if (!isset($value) || is_null($value))
				{
					$arSource[$key] = 'NULL';
				}
				elseif ($arTable['COLUMNS'][$key] == 0)
				{
					$arSource[$key] = $value;
				}
				elseif ($arTable['COLUMNS'][$key] == 1)
				{
					if (empty($value) && $value != '0')
					{
						$arSource[$key] = '\'\'';
					}
					else
					{
						$arSource[$key] = '0x' . bin2hex($value);
					}
				}
				elseif ($arTable['COLUMNS'][$key] == 2)
				{
					$arSource[$key] = "'" . $DB->ForSql($value) . "'";
				}
			}

			$strInsert .= "\n(" . implode(', ', $arSource) . ')';

			if ($arTable['KEY_COLUMN'])
			{
				$last_id = $arSource[$arTable['KEY_COLUMN']];
			}
			else
			{
				$last_id = $i;
			}

			if (mb_strlen($strInsert) > 102400)
			{
				$nodeDB->Query($strInsert, false, '', ['fixed_connection' => true]);
				$strInsert = '';
				$nodeDB->Query('
					UPDATE b_cluster_table
					SET LAST_ID = ' . $last_id . '
					,REC_COUNT = ' . $i . "
					WHERE ID = '" . $arTable['ID'] . "'
				", false, '', ['fixed_connection' => true]);
			}

			if (time() > $end_time)
			{
				break;
			}
		}

		if ($strInsert !== '')
		{
			$nodeDB->Query($strInsert, false, '', ['fixed_connection' => true]);
		}

		if ($arSource)
		{
			$nodeDB->Query('
				UPDATE b_cluster_table
				SET LAST_ID = ' . ($arTable['KEY_COLUMN'] <> '' ?
						$arSource[$arTable['KEY_COLUMN']] :
						$i) . '
				,REC_COUNT = ' . $i . "
				WHERE ID = '" . $arTable['ID'] . "'
			", false, '', ['fixed_connection' => true]);
		}
		elseif ($last_id <> '')
		{
			$nodeDB->Query('
				UPDATE b_cluster_table
				SET LAST_ID = ' . $last_id . '
				,REC_COUNT = ' . $i . "
				WHERE ID = '" . $arTable['ID'] . "'
			", false, '', ['fixed_connection' => true]);
		}
		else
		{
			$nodeDB->Query("
				DELETE FROM b_cluster_table
				WHERE ID = '" . $arTable['ID'] . "'
			", false, '', ['fixed_connection' => true]);
		}
	} while (time() < $end_time);

	$DB->Query('UNLOCK TABLES', false, '', ['fixed_connection' => true]);

	if ($strError)
	{
	}
	elseif (is_array($arTable))
	{
		$rs = $nodeDB->Query('select count(*) CNT from b_cluster_table', false, '', ['fixed_connection' => true]);
		$ar = $rs->Fetch();
		echo GetMessage('CLUWIZ_TABLE_PROGRESS', [
			'#table_name#' => $arTable['TABLE_NAME'],
			'#records#' => $i,
			'#tables#' => $ar['CNT'],
		]);
		echo '<script>BX.Cluster.SlaveStart.MoveTables(2)</script>';
	}
	else
	{
		echo '<p>',GetMessage('CLUWIZ_ALL_DONE'),'</p>';
		echo '<script>BX.Cluster.SlaveStart.EnableButton();</script>';
	}
}

function CreateNodeTable($nodeDB1, $nodeDB2, $TableName)
{
	$rs = $nodeDB1->Query('show create table `' . $nodeDB1->ForSql($TableName) . '`', true, '', ['fixed_connection' => true]);
	if (!$rs)
	{
		return GetMessage('CLUWIZ_QUERY_ERROR') . '<br>' . $nodeDB1->db_Error;
	}
	$ar = $rs->Fetch();

	if (!$ar)
	{
		return GetMessage('CLUWIZ_QUERY_ERROR');
	}
	elseif ($ar['Create Table'] != '')
	{
		$rs = $nodeDB2->Query($ar['Create Table'], true, '', ['fixed_connection' => true]);
		if (!$rs)
		{
			return GetMessage('CLUWIZ_QUERY_ERROR') . '<br>' . $nodeDB2->db_Error;
		}
		else
		{
			return '';
		}
	}
	elseif ($ar['Create View'] != '')
	{
		$rs = $nodeDB2->Query($ar['Create View'], true, '', ['fixed_connection' => true]);
		if (!$rs)
		{
			return GetMessage('CLUWIZ_QUERY_ERROR') . '<br>' . $nodeDB2->db_Error;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return '';
	}
}

function GetTableColumns($nodeDB, $TableName)
{
	$arResult = [];

	$sql = 'SHOW COLUMNS FROM `' . $TableName . '`';
	$res = $nodeDB->Query($sql, false, '', ['fixed_connection' => true]);
	while ($row = $res->Fetch())
	{
		if (preg_match('/^(\w*int|year|float|double|decimal)/', $row['Type']))
		{
			$arResult[$row['Field']] = 0;
		}
		elseif (preg_match('/^(\w*binary)/', $row['Type']))
		{
			$arResult[$row['Field']] = 1;
		}
		else
		{
			$arResult[$row['Field']] = 2;
		}
	}

	return $arResult;
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
