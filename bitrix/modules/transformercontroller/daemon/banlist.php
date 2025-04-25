<?php

namespace Bitrix\TransformerController\Daemon;

use Bitrix\TransformerController\Daemon\Dto\Ban;
use Bitrix\TransformerController\Daemon\Dto\Job;
use Psr\Log\LoggerInterface;

final class BanList
{
	/**
	 * @var Array<string, Ban>
	 */
	private array $bans;

	/**
	 * @param Ban[] $bans
	 */
	public function __construct(
		array $bans,

		private readonly LoggerInterface $logger,
	)
	{
		foreach ($bans as $ban)
		{
			$this->bans[$ban->domain] = $ban;
		}
	}

	public function checkIfBanned(Job $job): Result
	{
		$ban = $this->bans[$job->domain] ?? null;
		if (!$ban)
		{
			return new Result();
		}

		if ($ban->queueName && $job->queueName !== $ban->queueName)
		{
			//ban applied only to specific queue

			return new Result();
		}

		$isBanned = $ban->isPermanent || $ban->dateEndTimestamp > time();
		if (!$isBanned)
		{
			return new Result();
		}

		$this->logger->info(
			'Skip job {guid} because domain {domain} is banned',
			[
				'guid' => $job->guid,
				'domain' => $job->domain,
			]
		);

		return (new Result())->addError(
			new Error(
				"Domain {$job->domain} is banned",
				Error\Dictionary::DOMAIN_IS_BANNED,
			),
		);
	}
}
