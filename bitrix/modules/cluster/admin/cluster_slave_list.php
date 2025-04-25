<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/include.php';
IncludeModuleLangFile(__FILE__);

if (!$USER->isAdmin())
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$group_id = intval($_GET['group_id']);
if ($_GET['group_id'] !== 'all')
{
	if (!CClusterGroup::GetArrayByID($group_id))
	{
		$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
	}
}

$sTableID = 'tbl_cluster_slave_list';
$oSort = new CAdminSorting($sTableID, 'ID', 'ASC');
$lAdmin = new CAdminList($sTableID, $oSort);

if ($arID = $lAdmin->GroupAction())
{
	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}
		$ID = intval($ID);
		switch ($_REQUEST['action'])
		{
		case 'delete':
			$arNode = CClusterDBNode::GetByID($ID);
			if (
				is_array($arNode)
				&& ($arNode['ROLE_ID'] == 'SLAVE' || $arNode['ROLE_ID'] == 'MASTER')
				&& ($arNode['STATUS'] == 'READY' || $arNode['STATUS'] == 'PAUSED')
			)
			{
				CClusterDBNode::Delete($arNode['ID'], false);
			}
			break;
		case 'pause':
			CClusterSlave::Pause($ID);
			break;
		case 'resume':
			CClusterSlave::Resume($ID);
			break;
		case 'stop':
			CClusterSlave::Stop($ID);
			break;
		case 'skip_sql_error':
			$result = CClusterSlave::SkipSQLError($ID);
			if ($result !== true)
			{
				$lAdmin->AddGroupError($result, $ID);
			}
			break;
		}
	}
}

$arHeaders = [
	[
		'id' => 'ID',
		'content' => GetMessage('CLU_SLAVE_LIST_ID'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'FLAG',
		'content' => GetMessage('CLU_SLAVE_LIST_FLAG'),
		'align' => 'center',
		'default' => true,
	],
	[
		'id' => 'NAME',
		'content' => GetMessage('CLU_SLAVE_LIST_NAME'),
		'align' => 'left',
		'default' => true,
	],
	[
		'id' => 'BEHIND',
		'content' => GetMessage('CLU_SLAVE_LIST_BEHIND'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'STATUS',
		'content' => GetMessage('CLU_SLAVE_LIST_STATUS'),
		'align' => 'center',
		'default' => true,
	],
	[
		'id' => 'WEIGHT',
		'content' => GetMessage('CLU_SLAVE_LIST_WEIGHT'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'DESCRIPTION',
		'content' => GetMessage('CLU_SLAVE_LIST_DESCRIPTION'),
		'align' => 'left',
		'default' => false,
	],
	[
		'id' => 'DB_HOST',
		'content' => GetMessage('CLU_SLAVE_LIST_DB_HOST'),
		'align' => 'left',
		'default' => false,
	],
	[
		'id' => 'DB_NAME',
		'content' => GetMessage('CLU_SLAVE_LIST_DB_NAME'),
		'align' => 'left',
		'default' => false,
	],
	[
		'id' => 'DB_LOGIN',
		'content' => GetMessage('CLU_SLAVE_LIST_DB_LOGIN'),
		'align' => 'left',
		'default' => false,
	],
];

$lAdmin->AddHeaders($arHeaders);

$arFilter = [
	'=ROLE_ID' => ['MAIN', 'SLAVE', 'MASTER'],
];
if ($group_id > 0)
{
	$arFilter['=GROUP_ID'] = $group_id;
}

$rsData = CClusterDBNode::GetList(['ID' => 'ASC'], $arFilter);

if (!isset($_SESSION['SLAVE_LIST']))
{
	$_SESSION['SLAVE_LIST'] = [];
}

$rsData = new CAdminResult($rsData, $sTableID);
$Position = 0;
$bNote1Show = false;
$bHasMaster = false;
while ($arRes = $rsData->Fetch()):
	$row = $lAdmin->AddRow($arRes['ID'], $arRes);

	if ($arRes['ROLE_ID'] == 'MASTER' || $arRes['ROLE_ID'] == 'MAIN')
	{
		$bHasMaster = true;
	}

	$arSlaveStatus = CClusterSlave::GetStatus($arRes['ID']);
	if (is_array($arSlaveStatus) && $arRes['STATUS'] == 'OFFLINE')
	{
		CClusterDBNode::SetOnline($arRes['ID']);
		$arRes['STATUS'] = 'ONLINE';
	}

	if ($arRes['STATUS'] != 'OFFLINE')
	{
		$uptime = CClusterDBNode::GetUpTime($arRes['ID']);
	}
	else
	{
		$uptime = false;
	}

	if ($arRes['ID'] > 1)
	{
		$row->AddViewField('ID', '<a href="cluster_slave_edit.php?lang=' . LANGUAGE_ID . '&group_id=' . $arRes['GROUP_ID'] . '&ID=' . $arRes['ID'] . '">' . $arRes['ID'] . '</a>');
	}

	$Seconds_Behind_Master = 0;
	$Slave_IO_Running = 'Yes';
	$bHasSQLError = false;

	$html = '';
	if (is_array($arSlaveStatus))
	{
		$html .= '<table width="100%">';
		foreach ($arSlaveStatus as $key => $value)
		{
			if ($key == 'Seconds_Behind_Master')
			{
				$Seconds_Behind_Master = $value;
			}
			elseif ($key == 'Slave_IO_Running')
			{
				$Slave_IO_Running = $value;
			}

			if ($key == 'Read_Master_Log_Pos' || $key == 'Exec_Master_Log_Pos')
			{
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . $value . (
						$Position - $value > 0 ?
						' (<span style="color:red">-' . ($Position - $value) . '</span>)' :
						''
					) . '</td>
				</tr>
				';
			}
			elseif ($key == 'Seconds_Behind_Master')
			{
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . (
						$value > 0 ?
						'<span style="color:red">' . $value . '</span>' :
						'<span style="color:green">' . $value . '</span>'
					) . '</td>
				</tr>
				';
			}
			elseif ($key == 'Slave_IO_Running' || $key == 'Slave_SQL_Running')
			{
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . (
						$value == 'No' ?
						'<span style="color:red">' . $value . '</span>' :
						'<span style="color:green">' . $value . '</span>'
					) . '</td>
				</tr>
				';
			}
			elseif ($key == 'Com_select' || $key == 'Rows_returned')
			{
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . $value . (
						isset($_SESSION['SLAVE_LIST'][$arRes['ID']]) && $value > $_SESSION['SLAVE_LIST'][$arRes['ID']] ?
						' (<span style="color:green">+' . ($value - $_SESSION['SLAVE_LIST'][$arRes['ID']]) . '</span>)' :
						''
					) . '</td>
				</tr>
				';
			}
			elseif ($key == 'Last_SQL_Error')
			{
				$bHasSQLError = $value <> '';
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . (
						$value <> '' ?
						'<span style="color:red">' . $value . '</span>' :
						'<span style="color:green">' . $value . '</span>'
					) . '</td>
				</tr>
				';
			}
			elseif ($value !== null)
			{
				$html .= '
				<tr>
					<td width="50%" align=right>' . $key . ':</td>
					<td align=left>' . $value . '</td>
				</tr>
				';
			}

			if ($key == 'Com_select' || $key == 'Rows_returned')
			{
				$_SESSION['SLAVE_LIST'][$arRes['ID']] = $value;
			}
		}
		$html .= '</table>';
	}
	elseif ($arSlaveStatus <> '')
	{
		$html = $arSlaveStatus;
		$Slave_IO_Running = 'No';
	}
	else
	{
		$Slave_IO_Running = 'No';
	}

	if (
		($bHasSQLError || $Slave_IO_Running == 'No')
		&& $arRes['STATUS'] != 'READY'
		&& $arRes['STATUS'] != 'OFFLINE'
	)
	{
		$html = 'ERROR<br />' . $html;
	}
	else
	{
		$html = $arRes['STATUS'] . '<br />' . $html;
	}

	$row->AddViewField('STATUS', $html);

	$row->AddViewField('BEHIND', $Seconds_Behind_Master);

	if (
		$arRes['ACTIVE'] == 'Y'
		&& $arRes['STATUS'] == 'ONLINE'
		&& $Slave_IO_Running === 'Yes'
	)
	{
		$htmlFLAG = '<div class="lamp-green"></div>';
	}
	else
	{
		$htmlFLAG = '<div class="lamp-red"></div>';
	}

	if ($uptime === false)
	{
		$htmlFLAG .= GetMessage('CLU_SLAVE_NOCONNECTION');
	}
	else
	{
		$htmlFLAG .= GetMessage('CLU_SLAVE_UPTIME') . '<br>' . FormatDate([
			's' => 'sdiff',
			'i' => 'idiff',
			'H' => 'Hdiff',
			'' => 'ddiff',
		], time() - $uptime);
	}

	$row->AddViewField('FLAG', $htmlFLAG);

	if ($arRes['SELECTABLE'] == 'N' || $arRes['WEIGHT'] == 0)
	{
		if ($arRes['ROLE_ID'] == 'MAIN')
		{
			$row->AddViewField('WEIGHT', GetMessage('CLU_MAIN_LOAD'));
		}
		else
		{
			$row->AddViewField('WEIGHT', GetMessage('CLU_SLAVE_BACKUP'));
		}
	}

	$arActions = [];
	$arActions[] = [
		'ICON' => 'edit',
		'DEFAULT' => true,
		'TEXT' => GetMessage('CLU_SLAVE_LIST_EDIT'),
		'ACTION' => $lAdmin->ActionRedirect('cluster_slave_edit.php?lang=' . LANGUAGE_ID . '&group_id=' . ($arRes['GROUP_ID'] ?: 'all') . '&ID=' . $arRes['ID'])
	];

	if ($arRes['MASTER_ID'] <> '')
	{
		if ($arRes['STATUS'] == 'ONLINE')
		{
			$arActions[] = [
				'TEXT' => GetMessage('CLU_SLAVE_LIST_PAUSE'),
				'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'pause', 'group_id=' . ($group_id ?: 'all'))
			];
		}
		elseif ($arRes['STATUS'] == 'PAUSED')
		{
			$arActions[] = [
				'TEXT' => GetMessage('CLU_SLAVE_LIST_RESUME'),
				'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'resume', 'group_id=' . ($group_id ?: 'all'))
			];
			if (is_callable(['CClusterSlave', 'Stop']))
			{
				$arActions[] = [
					'TEXT' => GetMessage('CLU_SLAVE_LIST_STOP'),
					'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'stop', 'group_id=' . ($group_id ?: 'all'))
				];
			}
		}
	}

	if ($arRes['ROLE_ID'] == 'SLAVE' || $arRes['ROLE_ID'] == 'MASTER')
	{
		if ($arRes['STATUS'] == 'READY' && $group_id > 0)
		{
			$arActions[] = [
				'ICON' => 'delete',
				'TEXT' => GetMessage('CLU_SLAVE_LIST_DELETE'),
				'ACTION' => "if(confirm('" . GetMessage('CLU_SLAVE_LIST_DELETE_CONF') . "')) " . $lAdmin->ActionDoGroup($arRes['ID'], 'delete', 'group_id=' . $arRes['GROUP_ID'])
			];
			if ($arRes['ROLE_ID'] == 'MASTER')
			{
				$arActions[] = [
					'TEXT' => GetMessage('CLU_SLAVE_LIST_START_USING_DB'),
					'ACTION' => "javascript:StartWizard('bitrix:cluster.master_start','&__wiz_node_id=" . $arRes['ID'] . '&__wiz_group_id=' . $arRes['GROUP_ID'] . "')",
				];
			}
			else
			{
				$arActions[] = [
					'TEXT' => GetMessage('CLU_SLAVE_LIST_START_USING_DB'),
					'ACTION' => "javascript:StartWizard('bitrix:cluster.slave_start','&__wiz_node_id=" . $arRes['ID'] . '&__wiz_group_id=' . $arRes['GROUP_ID'] . "')",
				];
			}
		}
	}

	if ($bHasSQLError && is_callable(['CClusterSlave', 'SkipSQLError']))
	{
		$arActions[] = [
			'TEXT' => GetMessage('CLU_SLAVE_LIST_SKIP_SQL_ERROR'),
			'TITLE' => GetMessage('CLU_SLAVE_LIST_SKIP_SQL_ERROR_ALT'),
			'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'skip_sql_error', 'group_id=' . ($group_id ?: 'all'))
		];
	}

	if (!empty($arActions))
	{
		$row->AddActions($arActions);
	}
endwhile;

$lAdmin->AddFooter(
	[
		[
			'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $rsData->SelectedRowsCount(),
		],
		[
			'counter' => true,
			'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'),
			'value' => '0',
		],
	]
);

$url = 'cluster_slave_list.php?lang=' . urlencode(LANGUAGE_ID) . '&group_id=' . ($group_id ?: 'all');

$lAdmin->BeginPrologContent();
?>
<script>
	function StartWizard(name, params)
	{
		WizardWindow.Open(name, BX.bitrix_sessid() + params);
		BX.addCustomEvent(WizardWindow.currentDialog, 'onWindowClose', () => {<?=$lAdmin->ActionAjaxReload($url)?>});
	}
</script>
<?php
$lAdmin->EndPrologContent();

$aContext = [];
if (\Bitrix\Main\Application::getConnection()->getType() === 'pgsql')
{
	if ($group_id == 0)
	{
		$aContext[] = [
			'TEXT' => GetMessage('CLU_SLAVE_LIST_ADD'),
			'LINK' => "javascript:StartWizard('bitrix:cluster.pgsql','&__wiz_group_id=" . $group_id . "')",
			'TITLE' => GetMessage('CLU_SLAVE_LIST_ADD_TITLE'),
			'ICON' => 'btn_new',
		];
	}
}
elseif ($bHasMaster && $group_id > 0)
{
	$aContext[] = [
		'TEXT' => GetMessage('CLU_SLAVE_LIST_ADD'),
		'LINK' => "javascript:StartWizard('bitrix:cluster.slave_add','&__wiz_group_id=" . $group_id . "')",
		'TITLE' => GetMessage('CLU_SLAVE_LIST_ADD_TITLE'),
		'ICON' => 'btn_new',
	];
}
elseif ($group_id > 1)
{
	$aContext[] = [
		'TEXT' => GetMessage('CLU_SLAVE_LIST_MASTER_ADD'),
		'LINK' => "javascript:StartWizard('bitrix:cluster.master_add','&__wiz_group_id=" . $group_id . "')",
		'TITLE' => GetMessage('CLU_SLAVE_LIST_MASTER_ADD_TITLE'),
		'ICON' => 'btn_new',
	];
}
$aContext[] = [
	'TEXT' => GetMessage('CLU_SLAVE_LIST_REFRESH'),
	'LINK' => 'javascript:' . $lAdmin->ActionAjaxReload($url),
];

$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('CLU_SLAVE_LIST_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$lAdmin->DisplayList();

echo BeginNote(), GetMessage('CLU_SLAVE_LIST_NOTE'), EndNote();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
