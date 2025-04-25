<?php
IncludeModuleLangFile(__FILE__);

if (class_exists('cluster'))
{
	return;
}

class cluster extends CModule
{
	public $MODULE_ID = 'cluster';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

		$this->MODULE_NAME = GetMessage('CLU_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('CLU_MODULE_DESCRIPTION');
	}

	public function InstallDB($arParams = [])
	{
		/** @var CDatabase $DB */
		/** @var CMain $APPLICATION */
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();

		$this->errors = false;

		// Database tables creation
		if (!$DB->TableExists('b_cluster_dbnode'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/db/' . $connection->getType() . '/install.sql');

			if (!$this->errors)
			{
				$DB->Add('b_cluster_group', [
					'ID' => 1,
					'NAME' => GetMessage('CLU_GROUP_NO_ONE'),
				]);

				$DB->Add('b_cluster_dbnode', [
					'ID' => 1,
					'GROUP_ID' => 1,
					'ACTIVE' => 'Y',
					'ROLE_ID' => 'MAIN',
					'NAME' => GetMessage('CLU_MAIN_DATABASE'),
					'DESCRIPTION' => false,

					'DB_HOST' => false,
					'DB_NAME' => false,
					'DB_LOGIN' => false,
					'DB_PASSWORD' => false,

					'MASTER_ID' => false,
					'SERVER_ID' => false,
					'STATUS' => 'ONLINE',
				]);
			}
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			RegisterModule('cluster');
			CModule::IncludeModule('cluster');
			return true;
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		if (!array_key_exists('save_tables', $arParams) || $arParams['save_tables'] != 'Y')
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/db/mysql/uninstall.sql');
		}

		UnRegisterModule('cluster');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		return true;
	}

	public function InstallEvents()
	{
		return true;
	}

	public function UnInstallEvents()
	{
		return true;
	}

	public function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/wizards', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/wizards', true, true);
		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
		DeleteDirFilesEx('/bitrix/themes/.default/icons/cluster/');
		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step, $USER;
		if ($USER->isAdmin())
		{
			$step = intval($step);
			if (!CBXFeatures::IsFeatureEditable('Cluster'))
			{
				$this->errors = [GetMessage('MAIN_FEATURE_ERROR_EDITABLE')];
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->ThrowException(implode('<br>', $this->errors));
				$APPLICATION->IncludeAdminFile(GetMessage('CLU_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/step2.php');
			}
			elseif ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('CLU_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/step1.php');
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallFiles();
					CBXFeatures::SetFeatureEnabled('Cluster', true);
				}
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('CLU_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/step2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step, $USER;
		if ($USER->isAdmin())
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('CLU_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/unstep1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'save_tables' => $_REQUEST['save_tables'],
				]);
				$this->UnInstallFiles();
				CBXFeatures::SetFeatureEnabled('Cluster', false);
				$GLOBALS['errors'] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('CLU_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/cluster/install/unstep2.php');
			}
		}
	}
}
