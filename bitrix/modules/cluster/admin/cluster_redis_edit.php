<?php
use Bitrix\Main\Localization\Loc;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/prolog.php';
IncludeModuleLangFile(__FILE__);

if (!$USER->isAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$ID = intval($_REQUEST['ID'] ?? 0);
$server = CClusterRedis::getByID($ID);

$group_id = (int) $_REQUEST['group_id'];
if (
	is_array($server)
	&& !empty($server)
	&& $server['GROUP_ID'] != $group_id
)
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('CLU_REDIS_EDIT_TAB'),
		'ICON' => 'main_user_edit',
		'TITLE' => Loc::getMessage('CLU_REDIS_EDIT_TAB_TITLE'),
	],
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);

$strError = '';
$bVarsFromForm = false;
$message = null;
$cacheType = COption::GetOptionString('cluster', 'cache_type', 'memcache');

if (!extension_loaded('redis')  || $cacheType != 'redis')
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
	if ($cacheType != 'redis')
	{
		ShowError(Loc::getMessage('CLU_REDIS_DISABLED'));
	}
	else
	{
		ShowError(Loc::getMessage('CLU_REDIS_NO_EXTENTION'));
	}
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
	if ((isset($_REQUEST['save']) && $_REQUEST['save'] != '') || (isset($_REQUEST['apply']) && $_REQUEST['apply'] != ''))
	{
		$ob = new CClusterRedis;
		$fields = [
			'GROUP_ID' => $group_id,
			'HOST' => $_POST['HOST'],
			'PORT' => $_POST['PORT'],
		];

		if (is_array($server) && !empty($server))
		{
			$res = $ob->update($server['ID'], $fields);
		}
		else
		{
			$res = $ob->add($fields);
		}

		if ($res)
		{
			if (isset($_REQUEST['apply']) && $_REQUEST['apply'] != '')
			{
				LocalRedirect('/bitrix/admin/cluster_redis_edit.php?ID=' . $res . '&lang=' . LANGUAGE_ID . '&group_id=' . $group_id . '&' . $tabControl->ActiveTabParam());
			}
			else
			{
				LocalRedirect('/bitrix/admin/cluster_redis_list.php?lang=' . LANGUAGE_ID . '&group_id=' . $group_id);
			}
		}
		else
		{
			if ($e = $APPLICATION->GetException())
			{
				$message = new CAdminMessage(Loc::getMessage('CLU_REDIS_EDIT_SAVE_ERROR'), $e);
			}
			$bVarsFromForm = true;
		}
	}
}

ClearVars('str_');

if ($bVarsFromForm)
{
	$host = htmlspecialcharsbx($_REQUEST['HOST']);
	$port = (int) $_REQUEST['PORT'];
}
elseif (is_array($server) && !empty($server))
{
	$host = htmlspecialcharsbx($server['HOST']);
	$port = (int) $server['PORT'];
}
else
{
	$host = '127.0.0.1';
	$port = 6379;

	if (!CCluster::checkForServers(1))
	{
		$message = new CAdminMessage(['MESSAGE' => Loc::getMessage('CLUSTER_SERVER_COUNT_WARNING'), 'TYPE' => 'ERROR']);
	}
}

$APPLICATION->SetTitle(is_array($server) ? Loc::getMessage('CLU_REDIS_EDIT_EDIT_TITLE') : Loc::getMessage('CLU_REDIS_EDIT_NEW_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [[
	'TEXT' => Loc::getMessage('CLU_REDIS_EDIT_MENU_LIST'),
	'TITLE' => Loc::getMessage('CLU_REDIS_EDIT_MENU_LIST_TITLE'),
	'LINK' => 'cluster_redis_list.php?lang=' . LANGUAGE_ID . '&group_id=' . $group_id,
	'ICON' => 'btn_list',
]
];

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
{
	echo $message->Show();
}

?><form method="POST" action="<?=$APPLICATION->GetCurPage();?>"  enctype="multipart/form-data" name="editform" id="editform"><?php
		$tabControl->Begin();
		$tabControl->BeginNextTab();

		if (is_array($server) && isset($server['ID'])):
			?><tr><?php
				?><td><?=Loc::getMessage('CLU_REDIS_EDIT_ID')?>:</td><?php
				?><td><?=intval($server['ID']);?></td><?php
			?></tr><?php
		endif;

		?><tr><?php
			?><td width="40%"><?=Loc::getMessage('CLU_REDIS_EDIT_HOST')?>:</td><?php
			?><td width="60%"><input type="text" size="20" name="HOST" value="<?=$host?>"></td><?php
		?></tr><tr><?php
			?><td><?=Loc::getMessage('CLU_REDIS_EDIT_PORT')?>:</td><?php
			?><td><input type="text" size="6" name="PORT" value="<?=$port?>"></td><?php
		?></tr><?php

		$tabControl->Buttons(['back_url' => 'cluster_redis_list.php?lang=' . LANGUAGE_ID . '&group_id=' . $group_id]);
		echo bitrix_sessid_post();
		?><input type="hidden" name="lang" value="<?=LANGUAGE_ID?>"><?php
		?><input type="hidden" name="group_id" value="<?=$group_id?>"><?php

		if (is_array($server) && isset($server['ID'])):
			?><input type="hidden" name="ID" value="<?=intval($server['ID'])?>"><?php
		endif;

		$tabControl->End();
	?></form><?php

$tabControl->ShowWarnings('editform', $message);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
