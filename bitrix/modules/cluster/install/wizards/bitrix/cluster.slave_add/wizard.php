<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

abstract class CBaseSlaveAddWizardStep extends CWizardStep
{
	public $arMaster;

	public function InitStep()
	{
		global $DB;
		$wizard = $this->GetWizard();

		if (!CModule::IncludeModule('cluster'))
		{
			$this->SetError(GetMessage('CLUWIZ_NO_MODULE_ERROR'));
		}
		elseif ($DB->type != 'MYSQL')
		{
			$this->SetError(GetMessage('CLUWIZ_DATABASE_NOT_SUPPORTED'));
		}
		else
		{
			$arGroup = CClusterGroup::GetArrayByID(intval($wizard->GetVar('group_id')));
			if (!$arGroup)
			{
				$this->SetError(GetMessage('CLUWIZ_NO_GROUP_ERROR'));
			}
			else
			{
				$rsData = CClusterDBNode::GetList([] ,[
					'=ROLE_ID' => ['MAIN', 'MASTER'],
					'=GROUP_ID' => $arGroup['ID'],
					'=STATUS' => 'ONLINE',
				]);
				$this->arMaster = $rsData->Fetch();
				if (!$this->arMaster)
				{
					$this->SetError(GetMessage('CLUWIZ_NO_MASTER_ERROR'));
				}
			}
		}

		$wizard->SetDefaultVar('master_host', '');
		$wizard->SetDefaultVar('master_port', '3306');
		if ($this->arMaster)
		{
			if ($this->arMaster['ID'] == 1)
			{
				if (preg_match('/^(.+):(\\d+)$/', $GLOBALS['DB']->DBHost, $match))
				{
					$wizard->SetDefaultVar('master_host', $match[1]);
					$wizard->SetDefaultVar('master_port', $match[2]);
				}
			}
			else
			{
				if (preg_match('/^(.+):(\\d+)$/', $this->arMaster['DB_HOST'], $match))
				{
					$wizard->SetDefaultVar('master_host', $match[1]);
					$wizard->SetDefaultVar('master_port', $match[2]);
				}
			}
		}
	}

	public function ShowCheckList($arList)
	{
		if (count($arList) > 0)
		{
			$this->content .= '<ul>';
			foreach ($arList as $rec)
			{
				if ($rec['IS_OK'] == CClusterDBNodeCheck::OK)
				{
					$this->content .= '<li class="cluwiz_okli">' . $rec['MESSAGE'] . ' ... <span class="cluwiz_ok">' . GetMessage('CLUWIZ_CHEKED') . '</span></li>';
				}
				elseif ($rec['IS_OK'] == CClusterDBNodeCheck::WARNING)
				{
					$this->content .= '<li class="cluwiz_erli">' . $rec['MESSAGE'] . ' ... <span class="cluwiz_ok">' . GetMessage('CLUWIZ_CHEKED') . '</span></li>';
				}
				else
				{
					$this->content .= '<li class="cluwiz_erli">' . $rec['MESSAGE'] . '<p class="cluwiz_err">' . $rec['WIZ_REC'] . '</p></li>';
				}
			}
			$this->content .= '</ul>';
		}
	}

	public function CheckListHasNoError($arList)
	{
		foreach ($arList as $rec)
		{
			if ($rec['IS_OK'] == CClusterDBNodeCheck::ERROR)
			{
				return false;
			}
		}
		return true;
	}

	abstract public function ShowStepNoError();

	public function ShowStep()
	{
		if (count($this->GetErrors()) == 0)
		{
			$this->ShowStepNoError();
		}

		$this->content .= '<style>
			li.cluwiz_erli { list-style-image:url(/bitrix/themes/.default/images/lamp/red.gif) }
			li.cluwiz_okli { list-style-image:url(/bitrix/themes/.default/images/lamp/green.gif) }
			p.cluwiz_err { color:red }
			span.cluwiz_ok { color:green }
			</style>
		';
	}
}

//Check master DB parameters
class CSlaveAddStep1 extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP1_TITLE'));
		$this->SetStepID('step1');
		$this->SetCancelStep('cancel');
	}

	public function ShowStepNoError()
	{
		$this->content = GetMessage('CLUWIZ_STEP1_CONTENT');
		$this->content .= '<br />';

		$obCheck = new CClusterDBNodeCheck;
		$arCheckList = array_merge(
			$obCheck->MainNodeCommon($this->arMaster),
			$obCheck->MainNodeForReplication($this->arMaster)
		);

		$this->ShowCheckList($arCheckList);

		if ($this->CheckListHasNoError($arCheckList))
		{
			$this->SetNextStep('step2');
		}
		else
		{
			$this->SetNextStep('step1');
		}
	}
}

//Ask for connection credentials
class CSlaveAddStep2 extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP2_TITLE'));
		$this->SetPrevStep('step1');
		$this->SetStepID('step2');
		$this->SetNextStep('step4');
		$this->SetCancelStep('cancel');

		$wizard = $this->GetWizard();
		$wizard->SetDefaultVar('db_port', '3306');
	}

	public function ShowStepNoError()
	{
		global $DB;
		$wizard = $this->GetWizard();

		$inputParams = [
			'size' => 30,
			'maxsize' => 50,
		];
		$portParams = [
			'size' => 6,
			'maxsize' => 50,
		];
		$passwordParams = [
			'size' => 30,
			'maxsize' => 50,
			'autocomplete' => 'off',
		];
		$this->content = '
		<table cellpadding="1" cellspacing="0" border="0" width="100%">
			<tr valign="top">
				<td width="40%" align="right">' . GetMessage('CLUWIZ_STEP2_DB_HOST') . ':</td>
				<td width="60%">' . $this->ShowInputField('text', 'db_host', $inputParams) . '</td>
			</tr>
			<tr valign="top">
				<td width="40%" align="right">' . GetMessage('CLUWIZ_STEP2_MASTER_PORT') . ':</td>
				<td width="60%">' . $this->ShowInputField('text', 'db_port', $portParams) . '</td>
			</tr>
			<tr valign="top">
				<td align="right">' . GetMessage('CLUWIZ_STEP2_DB_NAME') . ':</td>
				<td>' . htmlspecialcharsbx($DB->DBName) . '</td>
			</tr>
			<tr valign="top">
				<td >&nbsp;</td>
				<td><span style="font-size:11px">' . GetMessage('CLUWIZ_STEP2_DB_NAME_HINT') . '</span></td>
			</tr>
			<tr valign="top">
				<td align="right">' . GetMessage('CLUWIZ_STEP2_DB_LOGIN') . ':</td>
				<td>' . $this->ShowInputField('text', 'db_login', $inputParams) . '</td>
			</tr>
			<tr valign="top">
				<td align="right">' . GetMessage('CLUWIZ_STEP2_DB_PASSWORD') . ':</td>
				<td>' . $this->ShowInputField('password', 'db_password', $passwordParams) . '</td>
			</tr>
		</table>
		';
		if (!$wizard->GetDefaultVar('master_host') || !$wizard->GetDefaultVar('master_port'))
		{
			$this->content .= '
			<br />' . GetMessage('CLUWIZ_STEP2_MASTER_CONN') . '
			<table cellpadding="2" cellspacing="0" border="0" width="100%">
				<tr valign="top">
					<td width="40%" align="right">' . GetMessage('CLUWIZ_STEP2_MASTER_HOST') . ':</td>
					<td width="60%">' . $this->ShowInputField('text', 'master_host', $inputParams) . '</td>
				</tr>
				<tr valign="top">
					<td width="40%" align="right">' . GetMessage('CLUWIZ_STEP2_MASTER_PORT') . ':</td>
					<td width="60%">' . $this->ShowInputField('text', 'master_port', $portParams) . '</td>
				</tr>
			</table>
			';
		}
	}
}

class CSlaveAddStep4 extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP4_TITLE'));
		$this->SetPrevStep('step2');
		$this->SetStepID('step4');
		$this->SetNextStep('step5');
		$this->SetCancelStep('cancel');
	}

	public function ShowStepNoError()
	{
		$wizard = $this->GetWizard();
		$wizard->SetVar('status', '');

		$obCheck = new CClusterDBNodeCheck;
		$IsReplicationRunning = $obCheck->SlaveNodeIsReplicationRunning(
			$wizard->GetVar('db_host') . ':' . $wizard->GetVar('db_port'),
			$GLOBALS['DB']->DBName,
			$wizard->GetVar('db_login'),
			$wizard->GetVar('db_password'),
			$wizard->GetVar('master_host', true),
			$wizard->GetVar('master_port', true)
		);

		if (is_object($IsReplicationRunning))
		{
			$this->content .= '<p>' . GetMessage('CLUWIZ_STEP4_SLAVE_IS_RUNNING') . '</p>';

			$arCheckList = array_merge(
				$obCheck->SlaveNodeCommon($IsReplicationRunning),
				$obCheck->SlaveNodeForReplication($IsReplicationRunning)
			);
			$this->ShowCheckList($arCheckList);
			$bNextStep = $this->CheckListHasNoError($arCheckList);
			if ($bNextStep)
			{
				$wizard->SetVar('status', 'online');
			}
		}
		elseif ($IsReplicationRunning === false)
		{
			$DB = $obCheck->SlaveNodeConnection(
				$wizard->GetVar('db_host') . ':' . $wizard->GetVar('db_port'),
				$GLOBALS['DB']->DBName,
				$wizard->GetVar('db_login'),
				$wizard->GetVar('db_password'),
				$wizard->GetVar('master_host', true),
				$wizard->GetVar('master_port', true),
				$this->arMaster['ID']
			);

			if (is_object($DB))
			{
				$arCheckList = array_merge(
					$obCheck->SlaveNodeCommon($DB),
					$obCheck->SlaveNodeForReplication($DB)
				);
				$this->ShowCheckList($arCheckList);
				$bNextStep = $this->CheckListHasNoError($arCheckList);
			}
			else
			{
				$this->content .= '<p class="cluwiz_err">' . $DB . '</p><p>' . GetMessage('CLUWIZ_STEP4_CONN_ERROR') . '</p>';
				$bNextStep = false;
			}
		}
		else
		{
			$this->content .= '<p class="cluwiz_err">' . $IsReplicationRunning . '</p><p>' . GetMessage('CLUWIZ_STEP4_CONN_ERROR') . '</p>';
			$bNextStep = false;
		}

		if ($bNextStep)
		{
			$this->SetNextStep('step5');
		}
		else
		{
			$this->SetNextStep('step4');
		}
	}
}

class CSlaveAddStep5 extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_STEP5_TITLE'));
		$this->SetPrevStep('step4');
		$this->SetStepID('step5');
		$this->SetNextStep('final');
		$this->SetCancelStep('cancel');
	}

	public function ShowStepNoError()
	{
		$inputParams = [
			'size' => 30,
			'maxsize' => 50,
		];
		$this->content = '
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="40%" align="right">' . GetMessage('CLUWIZ_STEP5_NAME') . ':</td>
				<td width="60%">' . $this->ShowInputField('text', 'node_name', $inputParams) . '</td>
			</tr>
		</table>
		';
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		$group_id = intval($wizard->GetVar('group_id'));

		if ($wizard->IsNextButtonClick())
		{
			$obNode = new CClusterDBNode;
			$obNode->Add([
				'ACTIVE' => 'Y',
				'ROLE_ID' => 'SLAVE',
				'GROUP_ID' => $group_id,
				'NAME' => $wizard->GetVar('node_name'),
				'DESCRIPTION' => false,
				'DB_HOST' => $wizard->GetVar('db_host') . ':' . $wizard->GetVar('db_port'),
				'DB_NAME' => $GLOBALS['DB']->DBName,
				'DB_LOGIN' => $wizard->GetVar('db_login'),
				'DB_PASSWORD' => $wizard->GetVar('db_password'),
				'MASTER_ID' => $this->arMaster['ID'],
				'MASTER_HOST' => $wizard->GetVar('master_host', true),
				'MASTER_PORT' => $wizard->GetVar('master_port', true),
				'SERVER_ID' => false,
				'STATUS' => $wizard->GetVar('status') === 'online' ? 'ONLINE' : 'READY',
				'SELECTABLE' => 'Y',
				'WEIGHT' => 100,
			]);
		}
	}
}

class CSlaveAddFinalStep extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_FINALSTEP_TITLE'));
		$this->SetStepID('final');
		$this->SetCancelStep('final');
		$this->SetCancelCaption(GetMessage('CLUWIZ_FINALSTEP_BUTTONTITLE'));
	}

	public function ShowStepNoError()
	{
	}

	public function ShowStep()
	{
		$this->content = GetMessage('CLUWIZ_FINALSTEP_CONTENT');
	}
}

class CSlaveAddCancelStep extends CBaseSlaveAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_CANCELSTEP_TITLE'));
		$this->SetStepID('cancel');
		$this->SetCancelStep('cancel');
		$this->SetCancelCaption(GetMessage('CLUWIZ_CANCELSTEP_BUTTONTITLE'));
	}

	public function ShowStepNoError()
	{
	}

	public function ShowStep()
	{
		$this->content = GetMessage('CLUWIZ_CANCELSTEP_CONTENT');
	}
}
