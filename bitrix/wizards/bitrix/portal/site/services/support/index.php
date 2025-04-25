<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

//if (WIZARD_IS_RERUN)
	//return;

if(!CModule::IncludeModule("support"))
	return;

CopyDirFiles(
	$_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/public/".LANGUAGE_ID."/support.php",
	WIZARD_SITE_PATH."services/support.php",
	false,
	true
);

$arCategories = Array(
	Array(
		'NAME'		=> GetMessage('CATEGORY_CORP_QUESTION'),
		'arrSITE'	=> Array(WIZARD_SITE_ID),
		'C_TYPE'  	=> 'C',
		'C_SORT'	=> 100,
		'EVENT1'	=> 'ticket',
	),
	Array(
		'NAME'		=> GetMessage('CATEGORY_COMPUTER_QUESTION'),
		'arrSITE'	=> Array(WIZARD_SITE_ID),
		'C_TYPE'  	=> 'C',
		'C_SORT'	=> 100,
		'EVENT1'	=> 'ticket',
	)
);

$newCategoryID = Array();
foreach ($arCategories as $arCategory)
{
	$categoryID = (int)CTicketDictionary::Add($arCategory);
	$newCategoryID[] = $categoryID;
}

$dbCategory = CTicketDictionary::GetList("s_id", "asc", Array("TYPE" => "C", "TYPE_EXACT_MATCH" => "Y"));
while ($arCategory = $dbCategory->Fetch())
{
	if (!in_array($arCategory["ID"], $newCategoryID))
		CTicketDictionary::Delete($arCategory["ID"]);
}

$APPLICATION->SetGroupRight("support", WIZARD_EMPLOYEES_GROUP, "R");
$APPLICATION->SetGroupRight("support", WIZARD_SUPPORT_GROUP, "W");

if (!defined("ADDITIONAL_INSTALL"))
{
	COption::SetOptionString("support","SUPPORT_MAX_FILESIZE","10000");
}
?>