<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Web\IpAddress;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\MicroService\LicenseVerification;

class Verification
{
	protected const MODULE_ID = 'transformercontroller';
	protected const OPTION_DOMAINS = 'allowed_domains';

	protected $isCheckByLicenseCode;
	protected $isEnabled = true;

	// isolated for tests
	private string $allowPrivateNetworkConstName = 'BX_TC_ALLOW_PRIVATE_NETWORK';

	public function __construct()
	{
		$this->isCheckByLicenseCode = $this->isCheckByLicenseCode();
	}

	public function isCheckByLicenseCode(): bool
	{
		return Loader::includeModule('microservice');
	}

	public function isCheckByDomain(): bool
	{
		return !$this->isCheckByLicenseCode();
	}

	public function setIsEnabled(bool $isEnabled): void
	{
		$this->isEnabled = $isEnabled;
	}

	public function isEnabled(): bool
	{
		return $this->isEnabled;
	}

	private function isPrivateDomainsAllowed(): bool
	{
		if (!defined($this->allowPrivateNetworkConstName))
		{
			return true;
		}

		return (bool)constant($this->allowPrivateNetworkConstName);
	}

	public function check(array $request): Result
	{
		$result = new Result();

		if(!$this->isEnabled())
		{
			return $result;
		}

		if(!$this->isDomainInBackUrlTheSame($request))
		{
			return $result->addError(new Error(
				'Wrong host in back_url',
				TimeStatistic::ERROR_CODE_BACK_URL_HOST_MISMATCH,
			));
		}

		$backUri = new Uri($request['params']['back_url'] ?? null);

		if (!$this->isPrivateDomainsAllowed())
		{
			if ($this->isDomainInBackUrlPrivate($backUri) || $this->isDomainInFileUrlPrivate($request))
			{
				return $result->addError(new Error(
					'Host in back url or file url is private. Server cant access your portal',
					TimeStatistic::ERROR_CODE_DOMAIN_IS_PRIVATE,
				));
			}
		}

		if ($this->isCheckByLicenseCode)
		{
			return $this->checkByMicroservice($request);
		}

		if(!$this->isDomainAllowed($backUri))
		{
			return $result->addError(new Error(
				'Domain is not allowed for this request',
				TimeStatistic::ERROR_CODE_RIGHT_CHECK_FAILED
			));
		}

		// we do not check license for standalone editions
		$result->setData([
			'TARIF' => 'stub',
			'LICENSE_KEY' => 'stub',
		]);

		return $result;
	}

	private function checkByMicroservice(array $request): Result
	{
		$licenseVerification = new LicenseVerification();
		$resultVerify = $licenseVerification->verify($request);
		if(!$resultVerify->isSuccess())
		{
			return $resultVerify;
		}
		$clientInfo = $resultVerify->getData()['client'] ?? [];
		if(empty($clientInfo['LICENSE_KEY']))
		{
			$clientInfo['LICENSE_KEY'] = $clientInfo['URL'] ?? null;
		}
		if(empty($clientInfo['TARIF']))
		{
			(new Result())->addError(new Error(
				'Missing data about license',
				TimeStatistic::ERROR_CODE_RIGHT_CHECK_FAILED
			));
		}

		return (new Result())->setData($clientInfo);
	}

	public function getAllowedDomains(): array
	{
		$options = Option::get(static::MODULE_ID, static::OPTION_DOMAINS);
		if(empty($options))
		{
			return [];
		}
		try
		{
			$domains = Json::decode($options);
		}
		catch (ArgumentException $exception)
		{
			return [];
		}

		if(empty($domains) || !is_array($domains))
		{
			return [];
		}

		return $domains;
	}

	public function setAllowedDomains(array $domains): void
	{
		Option::set(static::MODULE_ID, static::OPTION_DOMAINS, Json::encode($domains));
	}

	public function isDomainAllowed(Uri $backUri): bool
	{
		$domain = $backUri->getHost();
		$domains = $this->getAllowedDomains();

		if(empty($domains))
		{
			return false;
		}

		return in_array($domain, $domains, true);
	}

	public function isDomainInBackUrlTheSame(array $request): bool
	{
		$backUri = new Uri($request['params']['back_url'] ?? null);
		$domain = $backUri->getHost();
		$postUri = new Uri($request['BX_DOMAIN'] ?? null);
		$postDomain = $postUri->getHost();

		return ($domain === $postDomain);
	}

	private function isDomainInBackUrlPrivate(Uri $backUri): bool
	{
		return IpAddress::createByUri($backUri)->isPrivate();
	}

	private function isDomainInFileUrlPrivate(array $request): bool
	{
		if (empty($request['params']['file']))
		{
			return false;
		}

		$fileUrl = new Uri($request['params']['file']);

		return IpAddress::createByUri($fileUrl)->isPrivate();
	}
}
