<?php

namespace Bitrix\TransformerController;

use \Bitrix\Main\Data\Cache;

/** This class is designed to rely not on the create date of cache, but on expire date. */
class LicenseCache
{
	private $cacheInstance;
	private $name;
	private $path;
	private $ttl;

	/**
	 * LicenseCache constructor.
	 * @param Cache $cacheInstance Real cache where data is stored.
	 * @param string $name Unique key of the result.
	 * @param string $path Path to save cache.
	 * @param int $ttl Lifetime.
	 */
	public function __construct(Cache $cacheInstance, $name, $path, $ttl)
	{
		$this->cacheInstance = $cacheInstance;
		$this->name = $name;
		$this->path = $path;
		$this->ttl = $ttl;
	}

	/**
	 * Get data from real cache. Check it expire date and if cache is still valid - return data.
	 *
	 * @return null|array
	 */
	public function get()
	{
		if($this->cacheInstance->initCache($this->ttl, $this->name, $this->path))
		{
			$fullCache = $this->cacheInstance->getVars();
			if(isset($fullCache['dateExpire']) && time() < $fullCache['dateExpire'])
			{
				return $fullCache['result'];
			}
		}
		return null;
	}

	/**
	 * @param array $result Data to save.
	 * @param int $ttl Time to which cache will be valid.
	 */
	public function set($result, $ttl)
	{
		$this->cacheInstance->clean($this->name, $this->path);
		$this->cacheInstance->startDataCache($ttl);
		$fullCache = array(
			'result' => $result,
			'dateExpire' => time() + $ttl,
		);
		$this->cacheInstance->endDataCache($fullCache);
	}
}
