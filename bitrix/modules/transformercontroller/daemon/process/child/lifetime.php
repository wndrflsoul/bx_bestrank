<?php

namespace Bitrix\TransformerController\Daemon\Process\Child;

use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;

final class Lifetime
{
	private int $startTime;
	private int $endTime;
	private int $plannedLifeTime;

	public function __construct(
		private readonly Config $config
	)
	{
		$this->startTime = time();

		$this->plannedLifeTime = $this->resolveLifeTime();

		$this->endTime = $this->startTime + $this->plannedLifeTime;
	}

	private function resolveLifeTime(): int
	{
		return random_int($this->config->workerMinLifetime, $this->config->workerMaxLifetime);
	}

	public function getScheduledDieTime(): int
	{
		return $this->endTime;
	}

	public function getPlannedLifeTime(): int
	{
		return $this->plannedLifeTime;
	}

	public function isTimeToDie(): bool
	{
		return time() > $this->endTime;
	}

	public function scheduleDieAfterThisJobFinish(): void
	{
		$this->endTime = 0;
	}

	/**
	 * Finishes child execution. Should always be called instead of `die` in a child process.
	 *
	 * @return never
	 */
	public function die(): never
	{
		$this->cleanupBeforeDying();

		die();
	}

	public function cleanupBeforeDying(): void
	{
		DeleteQueue::getInstance()->flush();
	}
}
