<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CUser $USER */
global $USER;

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
$wizard->IncludeWizardLang('scripts/drop.php', $lang);

CModule::IncludeModule('cluster');

$node_id = $_REQUEST['node_id'];
if ($node_id <= 1)
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
else
{
	$arViewsToDelete = [];
	$rsViews = $nodeDB->Query('SHOW FULL TABLES IN ' . $nodeDB->DBName . " WHERE TABLE_TYPE LIKE 'VIEW'", false, '', ['fixed_connection' => true]);
	while ($arView = $rsViews->Fetch())
	{
		$arViewsToDelete[] = $arView['Tables_in_' . $nodeDB->DBName];
	}

	$arTablesToDelete = [];
	$rsTables = $nodeDB->Query('show tables', false, '', ['fixed_connection' => true]);
	while ($arTable = $rsTables->Fetch())
	{
		if (!in_array($arTable['Tables_in_' . $nodeDB->DBName], $arViewsToDelete))
		{
			$arTablesToDelete[] = $arTable['Tables_in_' . $nodeDB->DBName];
		}
	}

	if (empty($arTablesToDelete) && empty($arViewsToDelete))
	{
		echo GetMessage('CLUWIZ_ALL_DONE');
		echo '<script>BX.Cluster.MasterStart.EnableButton();</script>';
	}
	else
	{
		$lastDropped = '';
		$nodeDB->Query('SET FOREIGN_KEY_CHECKS=0', false, '', ['fixed_connection' => true]);
		$etime = time() + 2;
		while ((!empty($arTablesToDelete)) && ($etime > time()))
		{
			$table_name = array_pop($arTablesToDelete);
			$nodeDB->Query('drop table ' . $table_name, false, '', ['fixed_connection' => true]);
			$lastDropped = $table_name;
		}
		while ((!empty($arViewsToDelete)) && ($etime > time()))
		{
			$view_name = array_pop($arViewsToDelete);
			$nodeDB->Query('drop view ' . $view_name, false, '', ['fixed_connection' => true]);
			$lastDropped = $view_name;
		}
		echo GetMessage('CLUWIZ_TABLE_DROPPED', ['#table_name#' => $lastDropped]);
		echo '<br />';
		echo GetMessage('CLUWIZ_TABLE_PROGRESS', ['#tables#' => count($arTablesToDelete)]);
		echo '<script>BX.Cluster.MasterStart.DropTables()</script>';
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
