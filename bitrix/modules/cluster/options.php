<?php

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */

$moduleID = 'cluster';
$right = CMain::GetGroupRight($moduleID);

if ($right < 'R')
{
	return;
}

function getCacheType(string $moduleID): string
{
	return match(Bitrix\Main\Config\Option::get($moduleID, 'cache_type', 'memcache'))
	{
		'memcache' => 'memcache',
		'memcached' => 'memcached',
		'redis' => 'redis',
		'default' => 'memcache',
	};
}

function getCache(string $moduleID): string
{
	$cacheType = getCacheType($moduleID);
	if ($cacheType == 'memcache')
	{
		$cache = CClusterMemcache::class;
	}
	elseif ($cacheType == 'redis')
	{
		$cache = CClusterRedis::class;
	}
	else
	{
		$cache = Bitrix\Cluster\MemcachedClusterHelper::class;
	}
	return $cache;
}

Bitrix\Main\Loader::includeModule($moduleID);

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');

$options = [
	[
		'max_slave_delay',
		Loc::getMessage('CLUSTER_OPTIONS_MAX_SLAVE_DELAY') . ' ',
		['text', 6]
	],
	[
		'cache_type',
		Loc::getMessage('CLUSTER_OPTIONS_CACHE_TYPE'),
		[
			'select',
			[
				'memcache' => Loc::getMessage('CLUSTER_OPTIONS_CACHE_TYPE_MEMCACHE'),
				'memcached' => Loc::getMessage('CLUSTER_OPTIONS_CACHE_TYPE_MEMCACHED'),
				'redis' => Loc::getMessage('CLUSTER_OPTIONS_CACHE_TYPE_REDIS'),
			],
		],
	],
	[
		'heading',
		Loc::getMessage('CLUSTER_OPTIONS_REDIS_SETTINGS'),
		['heading', ''],
	],
	[
		'redis_pconnect',
		Loc::getMessage('CLUSTER_REDIS_PCONNECT_SETTING'),
		['checkbox', 'Y'],
	],
	[
		'failower_settings',
		Loc::getMessage('CLUSTER_OPTIONS_REDIS_FAILOWER_SETTINGS'),
		[
			'select',
			[
				'0' => Loc::getMessage('REDIS_OPTIONS_FAILOWER_NONE'),
				'1' => Loc::getMessage('REDIS_OPTIONS_FAILOWER_ERROR'),
				'2' => Loc::getMessage('REDIS_OPTIONS_FAILOVER_DISTRIBUTE'),
				'3' => Loc::getMessage('REDIS_OPTIONS_FAILOVER_DISTRIBUTE_SLAVES'),
			],
		],
	],
	[
		'redis_timeoit',
		Loc::getMessage('CLUSTER_OPTIONS_REDIS_TIMEOUT') . ' ',
		['text', 6],
	],
	[
		'redis_read_timeout',
		Loc::getMessage('CLUSTER_OPTIONS_REDIS_READ_TIMEOUT') . ' ',
		['text', 6],
	],
];

$tabs = [
	[
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('MAIN_TAB_SET'),
		'ICON' => $moduleID . '_settings',
		'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'),
	],
];

$tabControl = new CAdminTabControl('tabControl', $tabs);

if (
	$_SERVER['REQUEST_METHOD'] === 'POST'
	&& (
		(isset($_REQUEST['Update']) && $_REQUEST['Update'] !== '')
		|| (isset($_REQUEST['Apply']) && $_REQUEST['Apply'] !== '')
		|| (isset($_REQUEST['RestoreDefaults']) && $_REQUEST['RestoreDefaults'] !== '')
	)
	&& $right === 'W'
	&& check_bitrix_sessid()
)
{
	if (isset($_REQUEST['RestoreDefaults']) && $_REQUEST['RestoreDefaults'] !== '')
	{
		COption::RemoveOption($moduleID);
	}
	else
	{
		foreach ($options as $option)
		{
			$name = $option[0];
			$val = $_REQUEST[$name];
			if ($option[2][0] == 'checkbox' && $val != 'Y')
			{
				$val = 'N';
			}
			COption::SetOptionString($moduleID, $name, $val, $option[1]);
		}
	}

	$servers = [];
	$cache = getCache($moduleID);
	$rs = $cache::getList();
	while ($server = $rs->Fetch())
	{
		$servers[] = $server;
	}

	$cache::saveConfig($servers);

	if ($_REQUEST['back_url_settings'] != '')
	{
		if (
			(isset($_REQUEST['Apply']) && $_REQUEST['Apply'] !== '')
			|| (isset($_REQUEST['RestoreDefaults']) && $_REQUEST['RestoreDefaults'] !== '')
		)
		{
			LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($moduleID) . '&lang=' . urlencode(LANGUAGE_ID) . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings']) . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect($_REQUEST['back_url_settings']);
		}
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($moduleID) . '&lang=' . urlencode(LANGUAGE_ID) . '&' . $tabControl->ActiveTabParam());
	}
}

?><form method="post" action="<?php echo $APPLICATION->GetCurPage();?>?mid=<?php echo urlencode($moduleID);?>&amp;lang=<?php echo LANGUAGE_ID;?>"><?php

$tabControl->Begin();
$tabControl->BeginNextTab();

foreach ($options as $option)
{
	$val = '';
	$type = $option[2];
	if ($type[0] != 'heading')
	{
		$val = COption::GetOptionString($moduleID, $option[0]);
		?><tr><?php
			?><td width="40%" nowrap <?php echo ($type[0] == 'textarea') ? 'class="adm-detail-valign-top"' : ''?>><?php
				?><label for="<?php echo htmlspecialcharsbx($option[0])?>"><?php echo $option[1]?>:</label><?php
			?><td width="60%"><?php
	}

	if ($type[0] == 'checkbox')
	{
		?><input type="checkbox" name="<?php echo htmlspecialcharsbx($option[0]);?>" id="<?php echo htmlspecialcharsbx($option[0]);?>" value="Y" <?php echo ($val == 'Y') ? 'checked' : '';?>><?php
	}
	elseif ($type[0] == 'text')
	{
		?><input type="text" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val);?>" name="<?php echo htmlspecialcharsbx($option[0]);?>" id="<?php echo htmlspecialcharsbx($option[0]);?>"><?php
	}
	elseif ($type[0] == 'textarea')
	{
		?><textarea rows="<?php echo $type[1];?>" cols="<?php echo $type[2];?>" name="<?php echo htmlspecialcharsbx($option[0]);?>" id="<?php echo htmlspecialcharsbx($option[0]);?>"><?php echo htmlspecialcharsbx($val);?></textarea><?php
	}
	elseif ($type[0] == 'select')
	{
		?><select name="<?php echo htmlspecialcharsbx($option[0]);?>" ><?php
			foreach ($type[1] as $key => $value)
			{
				?><option value="<?php echo htmlspecialcharsbx($key);?>" <?php echo ($val == $key) ? 'selected="selected"' : ''?>><?php echo htmlspecialcharsEx($value);?></option><?php
			}
		?></select><?php
	}
	elseif ($type[0] == 'heading')
	{
		?><tr class="heading"><td colspan="2"><b><?php echo $option[1];?></b></td></tr><?php
	}

	if ($type[0] != 'heading')
	{
		?></td></tr><?php
	}
}

$tabControl->Buttons();

	?><input <?php echo ($right < 'W') ? 'disabled' : '' ?> type="submit" name="Update" value="<?php echo Loc::getMessage('MAIN_SAVE')?>" title="<?php echo Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save"><?php
	?><input <?php echo ($right < 'W') ? 'disabled' : '' ?> type="submit" name="Apply" value="<?php echo Loc::getMessage('MAIN_OPT_APPLY')?>" title="<?php echo Loc::getMessage('MAIN_OPT_APPLY_TITLE')?>"><?php
	if ($_REQUEST['back_url_settings'] != '')
	{
		?><input <?php echo ($right < 'W') ? 'disabled' : ''?> type="button" name="Cancel" value="<?php echo Loc::getMessage('MAIN_OPT_CANCEL')?>" title="<?php echo Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings']))?>'"><?php
		?><input type="hidden" name="back_url_settings" value="<?php echo htmlspecialcharsbx($_REQUEST['back_url_settings'])?>"><?php
	}
	?><input type="submit" name="RestoreDefaults" title="<?php echo Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" onclick="confirm('<?php echo addslashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS')?>"><?php
	echo bitrix_sessid_post();
	$tabControl->End();
?></form>