<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

if($ex = $APPLICATION->GetException())
	CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else
	CAdminMessage::ShowNote(GetMessage("IMOPENLINES_INSTALL_COMPLETE"));
?>
<div style="font-size: 12px;"></div>
<br>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>
