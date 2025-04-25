<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

abstract class CBaseDBNodeAddWizardStep extends CWizardStep
{
	public function InitStep()
	{
		global $DB;

		if (!CModule::IncludeModule('cluster'))
		{
			$this->SetError(GetMessage('CLUWIZ_DBNODE_ADD_NO_MODULE_ERROR'));
		}
		elseif ($DB->type != 'MYSQL')
		{
			$this->SetError(GetMessage('CLUWIZ_DBNODE_ADD_DATABASE_NOT_SUPPORTED'));
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
					$this->content .= '<li class="cluwiz_okli">' . $rec['MESSAGE'] . ' ... <span class="cluwiz_ok">' . GetMessage('CLUWIZ_DBNODE_ADD_CHEKED') . '</span></li>';
				}
				elseif ($rec['IS_OK'] == CClusterDBNodeCheck::WARNING)
				{
					$this->content .= '<li class="cluwiz_erli">' . $rec['MESSAGE'] . ' ... <span class="cluwiz_ok">' . GetMessage('CLUWIZ_DBNODE_ADD_CHEKED') . '</span></li>';
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
class CDBNodeAddStep1 extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_STEP1_TITLE'));
		$this->SetStepID('step1');
		$this->SetCancelStep('cancel');

		$wizard = $this->GetWizard();
		$wizard->SetDefaultVars([
			'node_name' => 'node',
		]);
	}

	public function ShowStepNoError()
	{
		$this->content = GetMessage('CLUWIZ_DBNODE_ADD_STEP1_CONTENT');
		$this->content .= '<br />';

		$obCheck = new CClusterDBNodeCheck;
		$arCheckList = $obCheck->MainNodeCommon(CClusterDBNode::GetByID(1));

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
class CDBNodeAddStep2 extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_STEP2_TITLE'));
		$this->SetPrevStep('step1');
		$this->SetStepID('step2');
		$this->SetNextStep('step3');
		$this->SetCancelStep('cancel');
	}

	public function ShowStepNoError()
	{
		$inputParams = [
			'size' => 30,
			'maxsize' => 50,
		];
		$passwordParams = [
			'size' => 30,
			'maxsize' => 50,
			'autocomplete' => 'off',
		];
		$this->content = '
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="40%" align="right">' . GetMessage('CLUWIZ_DBNODE_ADD_STEP2_DB_HOST') . ':</td>
				<td width="60%">' . $this->ShowInputField('text', 'db_host', $inputParams) . '</td>
			</tr>
			<tr>
				<td align="right">' . GetMessage('CLUWIZ_DBNODE_ADD_STEP2_DB_LOGIN') . ':</td>
				<td>' . $this->ShowInputField('text', 'db_login', $inputParams) . '</td>
			</tr>
			<tr>
				<td align="right">' . GetMessage('CLUWIZ_DBNODE_ADD_STEP2_DB_PASSWORD') . ':</td>
				<td>' . $this->ShowInputField('password', 'db_password', $passwordParams) . '</td>
			</tr>
			<tr>
				<td align="right">' . GetMessage('CLUWIZ_DBNODE_ADD_STEP2_DB_NAME') . ':</td>
				<td>' . $this->ShowInputField('text', 'db_name', $inputParams) . '</td>
			</tr>
		</table>
		';
	}
}

//Check master parameters for replication
class CDBNodeAddStep3 extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_STEP3_TITLE'));
		$this->SetPrevStep('step2');
		$this->SetStepID('step3');
		$this->SetCancelStep('cancel');
	}

	public function ShowStepNoError()
	{
		$wizard = $this->GetWizard();

		$obCheck = new CClusterDBNodeCheck;
		$DB = $obCheck->SlaveNodeConnection(
			$wizard->GetVar('db_host'),
			$wizard->GetVar('db_name'),
			$wizard->GetVar('db_login'),
			$wizard->GetVar('db_password')
		);
		if (is_object($DB))
		{
			$arCheckList = $obCheck->SlaveNodeCommon($DB);
			$this->ShowCheckList($arCheckList);
			$bNextStep = $this->CheckListHasNoError($arCheckList);
		}
		else
		{
			$this->content .= '<p class="cluwiz_err">' . $DB . '</p><p>' . GetMessage('CLUWIZ_DBNODE_ADD_STEP3_CONN_ERROR') . '</p>';
			$bNextStep = false;
		}

		if ($bNextStep)
		{
			$this->SetNextStep('step4');
		}
		else
		{
			$this->SetNextStep('step3');
		}
	}
}

class CDBNodeAddStep4 extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_STEP4_TITLE'));
		$this->SetPrevStep('step3');
		$this->SetStepID('step4');
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
					<td width="40%" align="right">' . GetMessage('CLUWIZ_DBNODE_ADD_STEP4_NAME') . ':</td>
					<td width="60%">' . $this->ShowInputField('text', 'node_name', $inputParams) . '</td>
				</tr>
			</table>
		';
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$obNode = new CClusterDBNode;
			$obNode->Add([
				'ACTIVE' => 'Y',
				'ROLE_ID' => 'MODULE',
				'NAME' => $wizard->GetVar('node_name'),
				'DESCRIPTION' => false,
				'DB_HOST' => $wizard->GetVar('db_host'),
				'DB_NAME' => $wizard->GetVar('db_name'),
				'DB_LOGIN' => $wizard->GetVar('db_login'),
				'DB_PASSWORD' => $wizard->GetVar('db_password'),
				'MASTER_ID' => false,
				'SERVER_ID' => false,
				'STATUS' => 'READY',
			]);
		}
	}
}

class CDBNodeAddFinalStep extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_FINALSTEP_TITLE'));
		$this->SetStepID('final');
		$this->SetCancelStep('final');
		$this->SetCancelCaption(GetMessage('CLUWIZ_DBNODE_ADD_FINALSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->content = GetMessage('CLUWIZ_DBNODE_ADD_FINALSTEP_CONTENT');
	}

	public function ShowStepNoError()
	{
	}
}
class CDBNodeAddCancelStep extends CBaseDBNodeAddWizardStep
{
	public function InitStep()
	{
		parent::InitStep();
		$this->SetTitle(GetMessage('CLUWIZ_DBNODE_ADD_CANCELSTEP_TITLE'));
		$this->SetStepID('cancel');
		$this->SetCancelStep('cancel');
		$this->SetCancelCaption(GetMessage('CLUWIZ_DBNODE_ADD_CANCELSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->content = GetMessage('CLUWIZ_DBNODE_ADD_CANCELSTEP_CONTENT');
	}

	public function ShowStepNoError()
	{
	}
}
