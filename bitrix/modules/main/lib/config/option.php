<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Config;

use Bitrix\Main;

class Option
{
	protected const CACHE_DIR = "b_option";

	protected static $options = [];
	protected static $loading = [];

	/**
	 * Returns a value of an option.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $default The default value to return, if a value doesn't exist.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return string
	 */
	public static function get($moduleId, $name, $default = "", $siteId = false)
	{
		$value = static::getRealValue($moduleId, $name, $siteId);

		if ($value !== null)
		{
			return $value;
		}

		if (isset(self::$options[$moduleId]["-"][$name]))
		{
			return self::$options[$moduleId]["-"][$name];
		}

		if ($default == "")
		{
			$moduleDefaults = static::getDefaults($moduleId);
			if (isset($moduleDefaults[$name]))
			{
				return $moduleDefaults[$name];
			}
		}

		return $default;
	}

	/**
	 * Returns the real value of an option as it's written in a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param bool|string $siteId The site ID.
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public static function getRealValue($moduleId, $name, $siteId = false)
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}
		if ($name == '')
		{
			throw new Main\ArgumentNullException("name");
		}

		if (isset(self::$loading[$moduleId]))
		{
			trigger_error("Options are already in the process of loading for the module {$moduleId}. Default value will be used for the option {$name}.", E_USER_WARNING);
		}

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$siteKey = ($siteId == ""? "-" : $siteId);

		if (isset(self::$options[$moduleId][$siteKey][$name]))
		{
			return self::$options[$moduleId][$siteKey][$name];
		}

		return null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $moduleId The module ID.
	 * @return array
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getDefaults($moduleId)
	{
		static $defaultsCache = [];

		if (isset($defaultsCache[$moduleId]))
		{
			return $defaultsCache[$moduleId];
		}

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
		{
			throw new Main\ArgumentOutOfRangeException("moduleId");
		}

		$path = Main\Loader::getLocal("modules/".$moduleId."/default_option.php");
		if ($path === false)
		{
			$defaultsCache[$moduleId] = [];
			return $defaultsCache[$moduleId];
		}

		include($path);

		$varName = str_replace(".", "_", $moduleId)."_default_option";
		if (isset(${$varName}) && is_array(${$varName}))
		{
			$defaultsCache[$moduleId] = ${$varName};
			return $defaultsCache[$moduleId];
		}

		$defaultsCache[$moduleId] = [];
		return $defaultsCache[$moduleId];
	}

	/**
	 * Returns an array of set options array(name => value).
	 *
	 * @param string $moduleId The module ID.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getForModule($moduleId, $siteId = false)
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$result = self::$options[$moduleId]["-"];

		if($siteId <> "" && !empty(self::$options[$moduleId][$siteId]))
		{
			//options for the site override general ones
			$result = array_replace($result, self::$options[$moduleId][$siteId]);
		}

		return $result;
	}

	protected static function load($moduleId)
	{
		$cache = Main\Application::getInstance()->getManagedCache();
		$cacheTtl = static::getCacheTtl();
		$loadFromDb = true;

		if ($cacheTtl !== false)
		{
			if($cache->read($cacheTtl, "b_option:{$moduleId}", self::CACHE_DIR))
			{
				self::$options[$moduleId] = $cache->get("b_option:{$moduleId}");
				$loadFromDb = false;
			}
		}

		if($loadFromDb)
		{
			self::$loading[$moduleId] = true;

			$con = Main\Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			// prevents recursion and cache miss
			self::$options[$moduleId] = ["-" => []];

			// prevents recursion on early stages with cluster module installed
			$pool = Main\Application::getInstance()->getConnectionPool();
			$pool->useMasterOnly(true);

			$query = "
				SELECT NAME, VALUE
				FROM b_option
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
			";

			$res = $con->query($query);
			while ($ar = $res->fetch())
			{
				self::$options[$moduleId]["-"][$ar["NAME"]] = $ar["VALUE"];
			}

			try
			{
				//b_option_site possibly doesn't exist

				$query = "
					SELECT SITE_ID, NAME, VALUE
					FROM b_option_site
					WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
				";

				$res = $con->query($query);
				while ($ar = $res->fetch())
				{
					self::$options[$moduleId][$ar["SITE_ID"]][$ar["NAME"]] = $ar["VALUE"];
				}
			}
			catch(Main\DB\SqlQueryException)
			{
			}

			$pool->useMasterOnly(false);

			if($cacheTtl !== false)
			{
				$cache->setImmediate("b_option:{$moduleId}", self::$options[$moduleId]);
			}

			unset(self::$loading[$moduleId]);
		}

		/*ZDUyZmZMWIzYTZhMDFhN2ExNDllYmNiMDhjOWE4MjQyNWVhZWY=*/$GLOBALS['____5758880']= array(base64_decode('Z'.'XhwbG9kZQ=='),base64_decode('cGFj'.'aw=='),base64_decode('b'.'W'.'Q'.'1'),base64_decode(''.'Y'.'29uc'.'3RhbnQ='),base64_decode('a'.'GFzaF'.'9ob'.'WFj'),base64_decode('c'.'3'.'RyY21w'),base64_decode('aX'.'Nfb2JqZW'.'N'.'0'),base64_decode('Y2'.'FsbF91c2'.'VyX2Z1bmM='),base64_decode('Y2F'.'sbF91c'.'2'.'V'.'yX2Z1bmM='),base64_decode('Y2'.'Fs'.'bF91c2'.'V'.'yX2Z1bmM='),base64_decode('Y2F'.'s'.'b'.'F91c2VyX2'.'Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___1643296605')){function ___1643296605($_1242500540){static $_333110105= false; if($_333110105 == false) $_333110105=array('bW'.'Fpbg'.'='.'=','bWFpb'.'g==','LQ==','flBBUkFNX01'.'BWF9VU0VSU'.'w==','bWFp'.'bg='.'=','LQ'.'==','flBBUk'.'F'.'N'.'X01BWF9V'.'U0VSUw'.'==','L'.'g==','SCo=','Yml'.'0cml4',''.'TElDRU'.'5TRV9L'.'R'.'V'.'k=','c2hhM'.'jU2','b'.'WFpbg==','L'.'Q==','UEFSQU1fTU'.'FYX1VTRVJ'.'T','V'.'VNF'.'Ug='.'=',''.'VVNF'.'Ug==','VVNFUg==','SXNBdXRob3JpemVk',''.'VVNFUg'.'==','SX'.'NBZG1'.'pbg'.'='.'=',''.'QVBQT'.'ElDQVR'.'JT04=',''.'UmVzdGFydEJ1ZmZl'.'cg==','TG9jYWxS'.'ZWRpcmVjdA==',''.'L'.'2xpY2'.'Vuc2Vfcm'.'Vzd'.'HJp'.'Y3'.'Rpb24ucGhw','bWFpbg==','LQ==','UE'.'F'.'SQU1fTUFYX1VTRVJT',''.'bWFpbg='.'=',''.'LQ==',''.'UEFSQU1f'.'TUFYX1VTRV'.'JT');return base64_decode($_333110105[$_1242500540]);}};if($moduleId === ___1643296605(0)){ if(isset(self::$options[___1643296605(1)][___1643296605(2)][___1643296605(3)])){ $_1177960092= self::$options[___1643296605(4)][___1643296605(5)][___1643296605(6)]; list($_283184117, $_916599891)= $GLOBALS['____5758880'][0](___1643296605(7), $_1177960092); $_57377452= $GLOBALS['____5758880'][1](___1643296605(8), $_283184117); $_892318116= ___1643296605(9).$GLOBALS['____5758880'][2]($GLOBALS['____5758880'][3](___1643296605(10))); $_652663103= $GLOBALS['____5758880'][4](___1643296605(11), $_916599891, $_892318116, true); if($GLOBALS['____5758880'][5]($_652663103, $_57377452) !==(848-2*424)){ self::$options[___1643296605(12)][___1643296605(13)][___1643296605(14)]= round(0+4+4+4); if(isset($GLOBALS[___1643296605(15)]) && $GLOBALS['____5758880'][6]($GLOBALS[___1643296605(16)]) && $GLOBALS['____5758880'][7](array($GLOBALS[___1643296605(17)], ___1643296605(18))) &&!$GLOBALS['____5758880'][8](array($GLOBALS[___1643296605(19)], ___1643296605(20)))){ $GLOBALS['____5758880'][9](array($GLOBALS[___1643296605(21)], ___1643296605(22))); $GLOBALS['____5758880'][10](___1643296605(23), ___1643296605(24), true);}} else{ self::$options[___1643296605(25)][___1643296605(26)][___1643296605(27)]= $_916599891;}} else{ self::$options[___1643296605(28)][___1643296605(29)][___1643296605(30)]= round(0+6+6);}}/**/
	}

	/**
	 * Sets an option value and saves it into a DB. After saving the OnAfterSetOption event is triggered.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $value The option value.
	 * @param string $siteId The site ID, if the option depends on a site.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function set($moduleId, $name, $value = "", $siteId = "")
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}
		if ($name == '')
		{
			throw new Main\ArgumentNullException("name");
		}

		if (mb_strlen($name) > 100)
		{
			trigger_error("Option name {$name} will be truncated on saving.", E_USER_WARNING);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$updateFields = [
			"VALUE" => $value,
		];

		if($siteId == "")
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME"];

			$sql = $sqlHelper->prepareMerge("b_option", $keyFields, $insertFields, $updateFields);
		}
		else
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"SITE_ID" => $siteId,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME", "SITE_ID"];

			$sql = $sqlHelper->prepareMerge("b_option_site", $keyFields, $insertFields, $updateFields);
		}

		$con->queryExecute(current($sql));

		static::clearCache($moduleId);

		static::loadTriggers($moduleId);

		$event = new Main\Event(
			"main",
			"OnAfterSetOption_".$name,
			array("value" => $value)
		);
		$event->send();

		$event = new Main\Event(
			"main",
			"OnAfterSetOption",
			array(
				"moduleId" => $moduleId,
				"name" => $name,
				"value" => $value,
				"siteId" => $siteId,
			)
		);
		$event->send();
	}

	protected static function loadTriggers($moduleId)
	{
		static $triggersCache = [];

		if (isset($triggersCache[$moduleId]))
		{
			return;
		}

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
		{
			throw new Main\ArgumentOutOfRangeException("moduleId");
		}

		$triggersCache[$moduleId] = true;

		$path = Main\Loader::getLocal("modules/".$moduleId."/option_triggers.php");
		if ($path === false)
		{
			return;
		}

		include($path);
	}

	protected static function getCacheTtl()
	{
		static $cacheTtl = null;

		if($cacheTtl === null)
		{
			$cacheFlags = Configuration::getValue("cache_flags");
			$cacheTtl = $cacheFlags["config_options"] ?? 3600;
		}
		return $cacheTtl;
	}

	/**
	 * Deletes options from a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param array $filter {name: string, site_id: string} The array with filter keys:
	 * 		name - the name of the option;
	 * 		site_id - the site ID (can be empty).
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentException
	 */
	public static function delete($moduleId, array $filter = array())
	{
		if ($moduleId == '')
		{
			throw new Main\ArgumentNullException("moduleId");
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$deleteForSites = true;
		$sqlWhere = '';
		$sqlWhereSite = '';

		foreach ($filter as $field => $value)
		{
			switch ($field)
			{
				case "name":
					if ($value == '')
					{
						throw new Main\ArgumentNullException("filter[name]");
					}
					$sqlWhere .= " AND NAME = '{$sqlHelper->forSql($value)}'";
					break;

				case "site_id":
					if ($value != '')
					{
						$sqlWhereSite = " AND SITE_ID = '{$sqlHelper->forSql($value, 2)}'";
					}
					else
					{
						$deleteForSites = false;
					}
					break;

				default:
					throw new Main\ArgumentException("filter[{$field}]");
			}
		}

		if($moduleId == 'main')
		{
			$sqlWhere .= "
				AND NAME NOT LIKE '~%'
				AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS')
			";
		}
		else
		{
			$sqlWhere .= " AND NAME <> '~bsm_stop_date'";
		}

		if($sqlWhereSite == '')
		{
			$con->queryExecute("
				DELETE FROM b_option
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
					{$sqlWhere}
			");
		}

		if($deleteForSites)
		{
			$con->queryExecute("
				DELETE FROM b_option_site
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}'
					{$sqlWhere}
					{$sqlWhereSite}
			");
		}

		static::clearCache($moduleId);
	}

	protected static function clearCache($moduleId)
	{
		unset(self::$options[$moduleId]);

		if (static::getCacheTtl() !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option:{$moduleId}", self::CACHE_DIR);
		}
	}

	protected static function getDefaultSite()
	{
		static $defaultSite;

		if ($defaultSite === null)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
			{
				$defaultSite = $context->getSite();
			}
		}
		return $defaultSite;
	}
}
