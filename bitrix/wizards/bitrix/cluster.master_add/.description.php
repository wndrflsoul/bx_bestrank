<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arWizardDescription = [
	'NAME' => GetMessage('CLUWIZ_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('CLUWIZ_DESCR_DESCRIPTION'),
	'ICON' => '',
	'COPYRIGHT' => GetMessage('CLUWIZ_DESCR_COPYRIGHT'),
	'VERSION' => '1.0.0',
	'STEPS' => [
		'CMasterAddStep1',
		'CMasterAddStep2',
		'CMasterAddStep4',
		'CMasterAddStep5',
		'CMasterAddFinalStep',
		'CMasterAddCancelStep',
	],
];
