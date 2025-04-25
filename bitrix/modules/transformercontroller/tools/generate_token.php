<?php

if (empty($argv[0]))
{
	die('generate_token.php should start from console only');
}

$docRoot = null;

if(!empty($argv[1]) && is_string($argv[1]))
{
	$docRoot = $argv[1];
}

if(empty($docRoot) || !is_dir($docRoot))
{
	$docRoot = realpath(__DIR__.'/../../../../');
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/cli/bootstrap.php';

$exit = static function (string $reason = ''): never {
	echo $reason . PHP_EOL;

	\CMain::FinalActions();
	die($reason);
};

if (!\Bitrix\Main\Loader::includeModule('transformercontroller'))
{
	$exit('Transformercontroller is not installed');
}

$tokenService = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('transformercontroller.service.token');

$generateResult = $tokenService->generate();
if (!$generateResult->isSuccess())
{
	$exit('Could not generate token: ' . PHP_EOL . implode(PHP_EOL, $generateResult->getErrorMessages()));
}

echo "Token: {$generateResult->getData()['token']}" . PHP_EOL;
echo "GUID: {$generateResult->getData()['guid']}" . PHP_EOL;

$exit();
