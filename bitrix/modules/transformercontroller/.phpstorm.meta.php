<?php
namespace PHPSTORM_META
{
	registerArgumentsSet('bitrix_transformercontroller_serviceLocator_codes',
		'transformercontroller.verification',
		'transformercontroller.service.token',
	);

	expectedArguments(
		\Bitrix\Main\DI\ServiceLocator::get(),
		0,
		argumentsSet('bitrix_transformercontroller_serviceLocator_codes'),
	);

	override(\Bitrix\Main\DI\ServiceLocator::get(0), map([
		'transformercontroller.verification' => \Bitrix\TransformerController\Verification::class,
		'transformercontroller.service.token' => \Bitrix\TransformerController\Service\Token::class,
	]));
}
