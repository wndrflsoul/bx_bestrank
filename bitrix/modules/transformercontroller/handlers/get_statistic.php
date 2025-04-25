<?php

use Bitrix\Main\Web\Json;

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

$action = \Bitrix\Main\Context::getCurrent()->getRequest()->getQuery('data');
if(!$action)
{
	$action = 'statistic';
}

$controller = new \Bitrix\TransformerController\Controllers\StatisticController();
$controller->setAction($action)->exec();
