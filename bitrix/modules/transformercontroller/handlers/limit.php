<?php

use Bitrix\Main\Web\Json;
use Bitrix\TransformerController\Controllers\LimitController;

/** @var CMain $APPLICATION */
global $APPLICATION;

if(is_object($APPLICATION))
	$APPLICATION->RestartBuffer();

if(!\Bitrix\Main\Loader::includeModule('transformercontroller'))
{
	echo Json::encode(array(
		'success' => false,
		'result' => array(
			/** @see \Bitrix\TransformerController\TimeStatistic::ERROR_CODE_MODULE_NOT_INSTALLED */
			'code' => 153,
			'msg' => 'Module transformercontroller isn`t installed',
		)
	));
	return;
}

$action = \Bitrix\Main\Context::getCurrent()->getRequest()->getQuery('action');
if(!$action)
{
	$action = 'getList';
}

$controller = new LimitController();
$controller->setAction($action)->exec();
