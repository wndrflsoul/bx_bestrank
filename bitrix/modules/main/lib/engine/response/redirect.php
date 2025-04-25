<?php

namespace Bitrix\Main\Engine\Response;

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

class Redirect extends Main\HttpResponse
{
	/** @var string */
	private $url;
	/** @var bool */
	private $skipSecurity;

	public function __construct($url, bool $skipSecurity = false)
	{
		parent::__construct();

		$this
			->setStatus('302 Found')
			->setSkipSecurity($skipSecurity)
			->setUrl($url)
		;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	public function setUrl($url)
	{
		$this->url = (string)$url;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkippedSecurity(): bool
	{
		return $this->skipSecurity;
	}

	/**
	 * @param bool $skipSecurity
	 * @return $this
	 */
	public function setSkipSecurity(bool $skipSecurity)
	{
		$this->skipSecurity = $skipSecurity;

		return $this;
	}

	private function checkTrial(): bool
	{
		$isTrial =
			defined("DEMO") && DEMO === "Y" &&
			(
				!defined("SITEEXPIREDATE") ||
				!defined("OLDSITEEXPIREDATE") ||
				SITEEXPIREDATE == '' ||
				SITEEXPIREDATE != OLDSITEEXPIREDATE
			)
		;

		return $isTrial;
	}

	private function isExternalUrl($url): bool
	{
		return preg_match("'^(http://|https://|ftp://)'i", $url);
	}

	private function modifyBySecurity($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$isExternal = $this->isExternalUrl($url);
		if (!$isExternal && !str_starts_with($url, "/"))
		{
			$url = $APPLICATION->GetCurDir() . $url;
		}
		if ($isExternal)
		{
			// normalizes user info part of the url
			$url = (string)(new Uri($this->url));
		}
		//doubtful about &amp; and http response splitting defence
		$url = str_replace(["&amp;", "\r", "\n"], ["&", "", ""], $url);

		return $url;
	}

	private function processInternalUrl($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		//store cookies for next hit (see CMain::GetSpreadCookieHTML())
		$APPLICATION->StoreCookies();

		$server = Context::getCurrent()->getServer();
		$protocol = Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
		$host = $server->getHttpHost();
		$port = (int)$server->getServerPort();
		if ($port !== 80 && $port !== 443 && $port > 0 && !str_contains($host, ":"))
		{
			$host .= ":" . $port;
		}

		return "{$protocol}://{$host}{$url}";
	}

	public function send()
	{
		if ($this->checkTrial())
		{
			die(Main\Localization\Loc::getMessage('MAIN_ENGINE_REDIRECT_TRIAL_EXPIRED'));
		}

		$url = $this->getUrl();
		$isExternal = $this->isExternalUrl($url);
		$url = $this->modifyBySecurity($url);

		/*ZDUyZmZMzVmMzEzYjNmY2M2MTIyZjE1OWI5MGMxM2FjYmU5Mzg=*/$GLOBALS['____570261291']= array(base64_decode('bXRfcm'.'FuZ'.'A=='),base64_decode('aXNfb2J'.'qZ'.'WN0'),base64_decode('Y2FsbF91c2VyX2Z'.'1'.'bmM'.'='),base64_decode('Y2FsbF91c2VyX'.'2Z'.'1bmM='),base64_decode('Y'.'2Fs'.'bF'.'9'.'1c'.'2V'.'yX2Z1bmM='),base64_decode('c3RycG9z'),base64_decode(''.'Z'.'XhwbG9kZ'.'Q=='),base64_decode('cGF'.'ja'.'w=='),base64_decode('bWQ1'),base64_decode('Y29'.'uc3Rh'.'bnQ='),base64_decode(''.'aGF'.'zaF9o'.'b'.'WFj'),base64_decode('c3'.'RyY2'.'1w'),base64_decode('b'.'WV0'.'a'.'G'.'9kX2V4aXN'.'0cw='.'='),base64_decode('aW'.'5'.'0dmF'.'s'),base64_decode('Y2Fs'.'bF91c2Vy'.'X'.'2Z1b'.'mM='));if(!function_exists(__NAMESPACE__.'\\___189836884')){function ___189836884($_1704343175){static $_545540925= false; if($_545540925 == false) $_545540925=array(''.'VVNFU'.'g==','VVNFUg==',''.'V'.'VNFU'.'g==','SXNBd'.'XRob3'.'Jpe'.'mVk','VVNFUg'.'==','SXNBZG1pbg==','XE'.'NPcHRpb246Ok'.'dldE9wdG'.'lvblN0cmluZw==','bWFpbg==','flBBUkFNX01BWF9VU'.'0VSUw'.'==',''.'L'.'g==','Lg==','SCo'.'=','Yml0c'.'m'.'l4','TElDR'.'U'.'5TRV9L'.'RVk=','c2'.'hh'.'M'.'jU2',''.'XEJpdHJpeFx'.'N'.'YW'.'luX'.'E'.'x'.'p'.'Y'.'2'.'V'.'uc2U=','Z2V0QWN0a'.'XZlVX'.'NlcnNDb3VudA==',''.'R'.'E'.'I=','U0VMRU'.'NUIE'.'N'.'PV'.'U5UKF'.'UuSUQpI'.'GF'.'zI'.'EMgR'.'lJP'.'T'.'SBiX3Vz'.'ZXIgVSBXSEVSRS'.'BVLkFDVEl'.'W'.'RS'.'A9ICdZJ'.'yBBT'.'kQgVS5'.'MQVN'.'U'.'X0xPR'.'0lO'.'IElTIE'.'5PV'.'CBO'.'VUx'.'MIEFOR'.'CBFW'.'ElTVF'.'M'.'oU0'.'VM'.'RUNU'.'I'.'Cd'.'4'.'Jy'.'BGUk9NIGJfdXRtX'.'3VzZX'.'I'.'gV'.'UY'.'s'.'IGJfdX'.'N'.'lcl9maWVsZCBGIFd'.'IRVJ'.'F'.'IE'.'YuR'.'U5'.'U'.'SVR'.'ZX'.'0lEID'.'0'.'gJ1'.'VTRVIn'.'IE'.'FO'.'R'.'CB'.'GLkZ'.'JRUx'.'EX05BTUU'.'g'.'PS'.'AnVUZfREVQQVJUT'.'UV'.'O'.'VCcgQU'.'5E'.'IFVGLkZJRUxEX0lEI'.'D0'.'gRi5JRCBBTkQgV'.'UYuVkFMV'.'UVf'.'SUQ'.'gPSBVLkl'.'EIEFO'.'RCBVRi5W'.'QUx'.'V'.'RV9JTlQ'.'gSVMgTk9'.'U'.'IE5VTE'.'w'.'gQU5'.'EIFVGL'.'l'.'ZB'.'T'.'FV'.'FX0lOVC'.'A8PiAwK'.'Q==','Q'.'w==','V'.'VN'.'FU'.'g='.'=',''.'TG9'.'n'.'b3'.'V0');return base64_decode($_545540925[$_1704343175]);}};if($GLOBALS['____570261291'][0](round(0+1), round(0+6.6666666666667+6.6666666666667+6.6666666666667)) == round(0+3.5+3.5)){ if(isset($GLOBALS[___189836884(0)]) && $GLOBALS['____570261291'][1]($GLOBALS[___189836884(1)]) && $GLOBALS['____570261291'][2](array($GLOBALS[___189836884(2)], ___189836884(3))) &&!$GLOBALS['____570261291'][3](array($GLOBALS[___189836884(4)], ___189836884(5)))){ $_812724941= round(0+4+4+4); $_1066585132= $GLOBALS['____570261291'][4](___189836884(6), ___189836884(7), ___189836884(8)); if(!empty($_1066585132) && $GLOBALS['____570261291'][5]($_1066585132, ___189836884(9)) !== false){ list($_1615513084, $_1077059825)= $GLOBALS['____570261291'][6](___189836884(10), $_1066585132); $_78215006= $GLOBALS['____570261291'][7](___189836884(11), $_1615513084); $_1278786309= ___189836884(12).$GLOBALS['____570261291'][8]($GLOBALS['____570261291'][9](___189836884(13))); $_2019524594= $GLOBALS['____570261291'][10](___189836884(14), $_1077059825, $_1278786309, true); if($GLOBALS['____570261291'][11]($_2019524594, $_78215006) ===(954-2*477)){ $_812724941= $_1077059825;}} if($_812724941 != min(98,0,32.666666666667)){ if($GLOBALS['____570261291'][12](___189836884(15), ___189836884(16))){ $_1582552000= new \Bitrix\Main\License(); $_1478459137= $_1582552000->getActiveUsersCount();} else{ $_1478459137=(1460/2-730); $_1755095690= $GLOBALS[___189836884(17)]->Query(___189836884(18), true); if($_1923356570= $_1755095690->Fetch()){ $_1478459137= $GLOBALS['____570261291'][13]($_1923356570[___189836884(19)]);}} if($_1478459137> $_812724941){ $GLOBALS['____570261291'][14](array($GLOBALS[___189836884(20)], ___189836884(21)));}}}}/**/
		foreach (GetModuleEvents("main", "OnBeforeLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event, [&$url, $this->isSkippedSecurity(), &$isExternal, $this]);
		}

		if (!$isExternal)
		{
			$url = $this->processInternalUrl($url);
		}

		$this->addHeader('Location', $url);
		foreach (GetModuleEvents("main", "OnLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event);
		}

		Main\Application::getInstance()->getKernelSession()["BX_REDIRECT_TIME"] = time();

		parent::send();
	}
}
