<?php
if(!$USER->IsAdmin())
	return;

use Bitrix\TransformerController\Settings;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/transformercontroller/options.php');

$moduleName = 'transformercontroller';
CModule::IncludeModule($moduleName);

$errorMessage = '';

$aTabs = array(
	array(
		"DIV" => "common", "TAB" => GetMessage("TRANSFORMERCONTROLLER_TAB_SETTINGS"), "TITLE" => GetMessage("TRANSFORMERCONTROLLER_TAB_TITLE_SETTINGS_2"),
	),
	array (
		"DIV" => "rabbit", "TAB" => GetMessage("TRANSFORMERCONTROLLER_TAB_RABBIT_SETTINGS"), "TITLE" => GetMessage("TRANSFORMERCONTROLLER_TAB_RABBIT_TITLE_SETTINGS_2"),
	),
	array (
		"DIV" => "queues", "TAB" => GetMessage("TRANSFORMERCONTROLLER_TAB_QUEUES_SETTINGS"), "TITLE" => GetMessage("TRANSFORMERCONTROLLER_TAB_QUEUES_SETTINGS"),
	),
	array (
		"DIV" => "status", "TAB" => GetMessage("TRANSFORMERCONTROLLER_TAB_STATUS_SETTINGS"), "TITLE" => GetMessage("TRANSFORMERCONTROLLER_TAB_STATUS_TITLE_SETTINGS"),
	)
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$verification = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('transformercontroller.verification');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if($request->isPost() && check_bitrix_sessid())
{
    if($request->getPost('Update') !== '')
    {
		$cronTime = (int)$request->getPost('CRON_TIME');
		if ($cronTime < 1)
		{
			$cronTime = 1;
		}
		elseif ($cronTime > 59)
		{
			$cronTime = 59;
		}

        $domains = explode(',', $request->getPost('DOMAINS') ?? '');
		$verification->setAllowedDomains($domains);
        \Bitrix\Main\Config\Option::set($moduleName, "debug", isset($_POST['DEBUG_MODE']));
        \Bitrix\Main\Config\Option::set($moduleName, "login", $request->getPost('QUEUE_LOGIN'));
        \Bitrix\Main\Config\Option::set($moduleName, "password", $request->getPost('QUEUE_PASSWORD'));
        \Bitrix\Main\Config\Option::set($moduleName, "host", $request->getPost('QUEUE_HOST'));
        \Bitrix\Main\Config\Option::set($moduleName, "port", $request->getPost('QUEUE_PORT'));
        \Bitrix\Main\Config\Option::set($moduleName, "vhost", $request->getPost('QUEUE_VHOST'));
        \Bitrix\Main\Config\Option::set($moduleName, "processes", $request->getPost('PROCESSES'));
        \Bitrix\Main\Config\Option::set($moduleName, "lifetime_from", $request->getPost('LIFETIME_FROM'));
        \Bitrix\Main\Config\Option::set($moduleName, "lifetime_to", $request->getPost('LIFETIME_TO'));
        \Bitrix\Main\Config\Option::set($moduleName, "cron_time", $cronTime);
        \Bitrix\Main\Config\Option::set($moduleName, "connection_time", $request->getPost('CONNECTION_TIME'));
        \Bitrix\Main\Config\Option::set($moduleName, "stream_time", $request->getPost('STREAM_TIME'));

        $cronSettings = ['processes' => $request->getPost('PROCESSES'), 'cron_time' => $cronTime];
        $queues = \Bitrix\TransformerController\Entity\QueueTable::getList()->fetchAll();
        foreach($queues as $queue)
        {
			$key = Settings::getKeyForWorkersByQueueName($queue['NAME']);
            $cronSettings[$key] = $request->getPost($key);
        }
        Settings::saveSettings($cronSettings);

    }
    elseif(mb_strlen($request->getPost('Generate')))
    {
        \Bitrix\TransformerController\Cron::addToCrontab();
    }
    elseif(mb_strlen($request->getPost('Clear')))
    {
        \Bitrix\TransformerController\Cron::deleteFromCrontab();
    }
    elseif(mb_strlen($request->getPost('Kill')))
    {
        Bitrix\TransformerController\Cron::killWorkers();
    }

    if (!empty($request->get('back_url_settings')))
    {
        LocalRedirect($request->get('back_url_settings'));
    }
    else
    {
        LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($request->get("back_url_settings"))."&".$tabControl->ActiveTabParam());
    }
}
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?echo LANG?>">
	<?php echo bitrix_sessid_post()?>
	<?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	if ($errorMessage):?>
		<tr>
			<td colspan="2" align="center"><b style="color:red"><?=htmlspecialcharsbx($errorMessage)?></b></td>
		</tr>
	<?endif;?>
	<tr>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_ACCOUNT_DEBUG")?>:</td>
		<td width="60%"><input type="checkbox" name="DEBUG_MODE" value="Y" <?=(COption::GetOptionInt($moduleName, "debug")? 'checked':'')?> /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_CONNECTION_TIME")?>:</td>
		<td width="60%"><input type="text" name="CONNECTION_TIME" value="<?=COption::GetOptionInt($moduleName, "connection_time", 3);?>" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_STREAM_TIME")?>:</td>
		<td width="60%"><input type="text" name="STREAM_TIME" value="<?=COption::GetOptionInt($moduleName, "stream_time", 7);?>" /></td>
	</tr>
    <?php if ($verification->isCheckByDomain()): ?>
        <tr>
            <td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_DOMAINS")?>:</td>
            <td width="60%"><textarea style="width: 80%;" name="DOMAINS"><?=htmlspecialcharsbx(implode(',', $verification->getAllowedDomains()));?></textarea></td>
        </tr>
    <?php endif;?>
	<?$tabControl->BeginNextTab();?>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_RABBIT_LOGIN")?>:</td>
		<td width="60%"><input type="text" name="QUEUE_LOGIN" value="<?=htmlspecialcharsbx(COption::GetOptionString($moduleName, "login"));?>" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_RABBIT_PASSWORD")?>:</td>
		<td width="60%"><input type="text" name="QUEUE_PASSWORD" value="<?=htmlspecialcharsbx(COption::GetOptionString($moduleName, "password"));?>" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_RABBIT_HOST")?>:</td>
		<td width="60%"><input type="text" name="QUEUE_HOST" value="<?=htmlspecialcharsbx(COption::GetOptionString($moduleName, "host"));?>" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_RABBIT_PORT")?>:</td>
		<td width="60%"><input type="text" name="QUEUE_PORT" value="<?=htmlspecialcharsbx(COption::GetOptionString($moduleName, "port"));?>" /></td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_RABBIT_VHOST")?>:</td>
		<td width="60%"><input type="text" name="QUEUE_VHOST" value="<?=htmlspecialcharsbx(COption::GetOptionString($moduleName, "vhost"));?>" /></td>
	</tr>
	<?$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="4"><?=GetMessage('TRANSFORMERCONTROLLER_TAB_QUEUES_SETTINGS_TIP', [
		        '#QUEUE_LINK#' => '/bitrix/tools/transformercontroller/queue.php',
            ]);?></td>
	</tr>
	<tr>
		<td align="right" style="font-weight: bold;"><?=GetMessage('TRANSFORMERCONTROLLER_TAB_QUEUES_NAME');?></td>
		<td align="right" style="font-weight: bold;"><?=GetMessage('TRANSFORMERCONTROLLER_TAB_QUEUES_SORT');?></td>
		<td align="right" style="font-weight: bold;"><?=GetMessage('TRANSFORMERCONTROLLER_TAB_QUEUES_WORKERS_DEFAULT');?></td>
		<td align="right" style="font-weight: bold;"><?=GetMessage('TRANSFORMERCONTROLLER_TAB_QUEUES_WORKERS_LOCAL');?></td>
	</tr>
	<?
	$queues = \Bitrix\TransformerController\Entity\QueueTable::getList()->fetchAll();
	$settings = new Settings();
	foreach($queues as $queue)
	{
		$localWorkers = $settings->getLocalWorkersForQueue($queue['NAME'] ?? '');
	?>
	<tr>
		<td align="right"><?=htmlspecialcharsbx($queue['NAME'] ?? '');?></td>
		<td align="right"><?=(int)($queue['SORT'] ?? 0);?></td>
		<td align="right"><?=(int)($queue['WORKERS'] ?? 0);?></td>
		<td align="right"><input
			name="<?=htmlspecialcharsbx(Settings::getKeyForWorkersByQueueName($queue['NAME'] ?? ''));?>"
			value="<?= $localWorkers !== null ? (int)$localWorkers : null ?>"
		/></td>
	</tr>
	<?}?>
	<?$tabControl->BeginNextTab();?>
	<?
	$runner = new Bitrix\TransformerController\Runner\SystemRunner();
	$statuses = array (
		'AMQP' => false,
		'EXEC' => true,
		'RABBIT' => null,
		'SOFFICE' => null,
		'WORKERS' => 0,
	);
	if (class_exists('AMQPConnection'))
	{
		$statuses['AMQP'] = true;
		$connectionParams = array(
			'login' => \Bitrix\Main\Config\Option::get($moduleName, 'login'),
			'password' => \Bitrix\Main\Config\Option::get($moduleName, 'password'),
			'host' => \Bitrix\Main\Config\Option::get($moduleName, 'host'),
			'port' => \Bitrix\Main\Config\Option::get($moduleName, 'port'),
			'vhost' => \Bitrix\Main\Config\Option::get($moduleName, 'vhost'),
		);
		try
		{
			$connection = new \AMQPConnection($connectionParams);
			$connection->connect();
			$statuses['RABBIT'] = $connection->isConnected();
			$connection->disconnect();
		}
		catch (\AMQPException)
		{
			$statuses['RABBIT'] = false;
		}
	}
	$pwdResult = $runner->run('pwd');
	if (!$pwdResult || $pwdResult === null)
	{
		$statuses['EXEC'] = false;
	}
	else
	{
		$statuses['WORKERS'] = \Bitrix\TransformerController\Cron::getProcesses();
		$libreOfficePath = \Bitrix\TransformerController\Document::getLibreOfficePath();
		$officeCheck = $runner->run(escapeshellcmd($libreOfficePath) . ' --version');
		if($officeCheck)
		{
			if(!is_array($officeCheck))
			{
				$officeCheck = array($officeCheck);
			}
			foreach($officeCheck as $versionString)
			{
				if(mb_strpos($versionString, 'LibreOffice') !== false)
				{
					$statuses['SOFFICE'] = true;
					break;
				}
			}
		}
	}
	foreach ($statuses as $code => $status)
	{?>
		<tr>
			<td width="40%"><?=GetMessage("TRANSFORMERCONTROLLER_STATUS_".$code)?>:</td>
			<td width="60%"><?
				if (is_int($status))
					echo $status;
				elseif ($status === true)
					echo '<b style="color: green;">'.GetMessage("TRANSFORMERCONTROLLER_STATUS_LABEL_OK").'</b>';
				elseif ($status === false)
					echo '<b style="color: red;">'.GetMessage("TRANSFORMERCONTROLLER_STATUS_LABEL_FAIL").'</b>';
				else
					echo '<b style="color: grey;">'.GetMessage("TRANSFORMERCONTROLLER_STATUS_LABEL_UNKNONW").'</b>';
			?></td>
		</tr>
	<?}
	?>
	<tr>
		<td></td>
	</tr>
	<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
	<input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
	<?$tabControl->End();?>
</form>
