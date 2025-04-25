<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class LicenseVerify
{
	const TYPE_BITRIX24 = 'B24';
	const TYPE_CP = 'CP';

	const ERROR_LICENSE_NOT_FOUND = 'LICENSE_NOT_FOUND';
	const ERROR_WRONG_SIGN = 'WRONG_SIGN';
	const ERROR_LICENSE_DEMO = 'LICENSE_DEMO';
	const ERROR_LICENSE_NOT_ACTIVE = 'LICENSE_NOT_ACTIVE';

	const VERIFY_RESULT_ERROR_FOUND = 'error';
	const VERIFY_RESULT_ERROR_CONNECTION = 'CONNECT_ERROR';

	const CACHE_TTL = 604800; // one week
	const CACHE_TTL_CONNECTION = 300;
	const CACHE_TTL_ERROR = 86400;
	const CACHE_PATH = '/bx/transformercontroller/license/';

	private $result;

	private $type = null;
	private $license = '';
	private $params = array();


	/**
	 * Class for checking license.
	 *
	 * @param string $type Type of license. Must be self::TYPE_BITRIX24 or self::TYPE_CP.
	 * @param string $license License string. For type self::TYPE_BITRIX24 - portal domain without protocol, for type self::TYPE_CP - LICENSE_KEY_HEAD.
	 * @param array $params Parameters and signature.
	 *
	 */
	public function __construct($type, $license, $params)
	{
		$this->result = new Result();
		$this->type = $type;
		$this->license = $license;
		$this->params = $params;
		if(!in_array($type, array(self::TYPE_BITRIX24, self::TYPE_CP)))
		{
			$this->result->addError(new Error('Type error. Must be self::TYPE_BITRIX24 or self::TYPE_CP.'));
		}
		if(empty($license))
		{
			$this->result->addError(new Error('License string error. For type self::TYPE_BITRIX24 - portal domain without protocol, for type self::TYPE_CP - LICENSE_KEY_HEAD.'));
		}
		if(!is_array($params) || empty($params))
		{
			$this->result->addError(new Error('Parameters and signature isn\'t specified.'));
		}
	}

	/**
	 * Get verification response from cache. If there is none - get it from server.
	 * Parse response, fill errors.
	 * Return true if license is active.
	 *
	 * @return Result
	 */
	public function getResult()
	{
		if(defined('TRANSFORMER_CONTROLLER_SKIP_LICENSE_VERIFY') && TRANSFORMER_CONTROLLER_SKIP_LICENSE_VERIFY === true)
		{
			$this->result->setData(array(
					'result' => array(
						'TARIF' => 'stub',
						'LICENSE_KEY' => 'stub',
					)
				)
			);
		}
		elseif($this->result->isSuccess())
		{
			$cacheName = $this->type==self::TYPE_BITRIX24? md5($this->license): $this->license;
			$cachePath = self::CACHE_PATH.$this->type.'/';
			$cacheExpire = self::CACHE_TTL;
			$cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
			$licenseCache = new LicenseCache($cacheInstance, $cacheName, $cachePath, self::CACHE_TTL);

			$licenseInfo = $licenseCache->get();
			if($licenseInfo === null)
			{
				$licenseInfo = $this->verify();
				if($licenseInfo['status'] == self::VERIFY_RESULT_ERROR_FOUND)
				{
					if(isset($licenseInfo['code']) && $licenseInfo['code'] == self::VERIFY_RESULT_ERROR_CONNECTION)
					{
						$cacheExpire = self::CACHE_TTL_CONNECTION;
					}
					else
					{
						$cacheExpire = self::CACHE_TTL_ERROR;
					}
				}
				$licenseCache->set($licenseInfo, $cacheExpire);
			}

			if($licenseInfo['status'] == self::VERIFY_RESULT_ERROR_FOUND)
			{
				$this->result->addError(new Error($licenseInfo['text']));
			}
			$this->result->setData($licenseInfo);
		}

		return $this->result;
	}

	/**
	 * Compose query to the license server and return response
	 *
	 * @return array
	 */
	private function verify()
	{
		$params = $this->params;
		$params['BX_DEMO'] = 'y';

		$httpClient = new \Bitrix\Main\Web\HttpClient(array(
			'socketTimeout' => Option::get('transformercontroller', 'connection_time', 3),
			'streamTimeout' => Option::get('transformercontroller', 'stream_time', 7),
		));
		$httpClient->setHeader('User-Agent', 'Bitrix Transformer Controller');
		$answer = $httpClient->post($this->getValidationUrl(), $params);

		if($answer && $httpClient->getStatus() == '200')
		{
			try
			{
				$answer = \Bitrix\Main\Web\Json::decode($httpClient->getResult());
			}
			catch(ArgumentException $e)
			{
				$answer = array(
					'status' => self::VERIFY_RESULT_ERROR_FOUND,
					'code' => self::VERIFY_RESULT_ERROR_CONNECTION,
					'text' => 'Cant parse answer from server.',
					'data' => array($httpClient->getError(), $httpClient->getResult())
				);
			}
		}
		else
		{
			$answer = array(
				'status' => self::VERIFY_RESULT_ERROR_FOUND,
				'code' => self::VERIFY_RESULT_ERROR_CONNECTION,
				'text' => 'Cant parse answer from server.',
				'data' => array($httpClient->getError(), $httpClient->getResult())
			);
		}

		return $answer;
	}

	private function getValidationUrl(): string
	{
		if(defined('TRANSFORMER_CONTROLLER_LICENSE_VALIDATION_URL') && is_string(TRANSFORMER_CONTROLLER_LICENSE_VALIDATION_URL))
		{
			return TRANSFORMER_CONTROLLER_LICENSE_VALIDATION_URL;
		}

		return 'https://www.1c-bitrix.ru/buy_tmp/verify.php';
	}

	/**
	 * Return current errors.
	 * @return array
	 */
	public function getError()
	{
		return implode(PHP_EOL, $this->result->getErrorMessages());
	}
}