<?php

$error = false;

$requiredParams = array(
	'command', 'params', 'BX_LICENCE', 'BX_DOMAIN', 'BX_TYPE', 'BX_VERSION', 'BX_HASH'
);

if($_SERVER['REQUEST_METHOD'] != 'POST')
{
	$error = true;
}

foreach($requiredParams as $param)
{
	if(!isset($_POST[$param]) || empty($_POST[$param]))
	{
		$error = true;
		break;
	}
}

if($error)
{
	header($_SERVER["SERVER_PROTOCOL"].' 403 Access Denied', true, 403);
	die();
}

define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/json; charset=UTF-8');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/transformercontroller/handlers/add_queue.php");

CMain::FinalActions();
die();