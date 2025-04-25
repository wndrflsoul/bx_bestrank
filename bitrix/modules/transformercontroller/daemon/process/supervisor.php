<?php

namespace Bitrix\TransformerController\Daemon\Process;

use Bitrix\TransformerController\Daemon\BanList;
use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Psr\Log\LoggerInterface;

final class Supervisor
{
	private array $children = [];
	private array $gracefulShutdownSignalSent = [];
	private array $terminationSent = [];
	private ?BanList $banList = null;
	private int $banListLastFetchedTimestamp = 0;

	public function __construct(
		private readonly Config $config,
		private readonly LoggerInterface $logger,
	)
	{
		foreach (array_keys($this->config->queueWorkers) as $queueName)
		{
			$this->children[$queueName] = [];
		}
	}

	public function start(): void
	{
		$this->logger->notice('Start supervisor', ['workers' => $this->config->queueWorkers]);

		Signal::subscribeToGracefulShutdownSignal($this->gracefulShutdown(...));

		$this->bootstrap();

		while (true)
		{
			Signal::processPendingSignals();

			if ($this->shouldPrepareBanList())
			{
				$this->banList = $this->prepareBanList();
				$this->banListLastFetchedTimestamp = time();
			}

			foreach ($this->config->queueWorkers as $queueName => $desiredNumberOfWorkers)
			{
				$this->actualizeChildrenList($queueName);

				$this->ensureExactNumberOfChildrenIsRunning($queueName, $desiredNumberOfWorkers);
			}

			sleep(1);
		}
	}

	private function bootstrap(): void
	{
		Resolver::setCurrent($this->config);
	}

	private function shouldPrepareBanList(): bool
	{
		if (!$this->banList)
		{
			return true;
		}

		$secondsSinceLastFetch = time() - $this->banListLastFetchedTimestamp;

		return $secondsSinceLastFetch > $this->config->banListActualizationPeriod;
	}

	private function prepareBanList(): BanList
	{
		$getBansResult = (new Request\Controller\GetBans())
			->setLoggerFluently(LoggerFactory::getInstance()->create($this->config, ['type' => 'http']))
			->send()
		;

		$bans = $getBansResult->getDataKey('bans') ?? [];

		return new BanList($bans, LoggerFactory::getInstance()->create($this->config, ['type' => 'ban_list']));
	}

	private function actualizeChildrenList(string $queueName): void
	{
		foreach ($this->children[$queueName] as $childPid)
		{
			$waitPidResult = pcntl_waitpid($childPid, $status, WNOHANG);
			$isExited = $waitPidResult !== 0;

			if (!$isExited)
			{
				continue;
			}

			$isExitedWithError = $waitPidResult === -1;
			$isNormalExitCode = pcntl_wifexited($status);
			$childExitCode = pcntl_wexitstatus($status);

			unset(
				$this->children[$queueName][$childPid],
				$this->gracefulShutdownSignalSent[$childPid],
				$this->terminationSent[$childPid],
			);

			if ($isExitedWithError || !$isNormalExitCode)
			{
				$this->logger->error(
					'Child {childPid} finished with error exit code: {exitCode}',
					[
						'childPid' => $childPid,
						'exitCode' => $childExitCode,
					]
				);
			}
			else
			{
				$this->logger->info(
					'Child {childPid} finished with ok exit code: {exitCode}',
					[
						'childPid' => $childPid,
						'exitCode' => $childExitCode,
					]
				);
			}
		}
	}

	private function ensureExactNumberOfChildrenIsRunning(string $queueName, int $desiredNumberOfWorkersForQueue): void
	{
		$currentlyRunningWorkersForQueue = count($this->children[$queueName]);
		if ($currentlyRunningWorkersForQueue === $desiredNumberOfWorkersForQueue)
		{
			return;
		}

		while ($currentlyRunningWorkersForQueue < $desiredNumberOfWorkersForQueue)
		{
			$this->fork($queueName);

			$currentlyRunningWorkersForQueue++;
		}

		if ($currentlyRunningWorkersForQueue > $desiredNumberOfWorkersForQueue)
		{
			foreach ($this->children[$queueName] as $pid)
			{
				$this->shutdownChild($pid);

				$currentlyRunningWorkersForQueue--;

				if ($currentlyRunningWorkersForQueue <= $desiredNumberOfWorkersForQueue)
				{
					break;
				}
			}
		}
	}

	private function fork(string $queueName): void
	{
		$pid = pcntl_fork();
		if ($pid > 0)
		{
			// we are in parent thread
			$this->children[$queueName][$pid] = $pid;
		}
		elseif ($pid < 0)
		{
			// we are in parent thread, but fork failed
			$this->logger->critical('Failed to fork process');
		}
		else
		{
			// we are in child thread

			$childLogger = LoggerFactory::getInstance()->create(
				$this->config,
				[
					'type' => 'worker',
				]
			);

			$child = new Child($queueName, $this->config, $this->banList, $childLogger);
			// child thread is never returned from this call - it's forever-blocking
			$child->start();
		}
	}

	private function hasRunningChildren(): bool
	{
		foreach ($this->children as $pids)
		{
			if (!empty($pids))
			{
				return true;
			}
		}

		return false;
	}

	public function gracefulShutdown(): never
	{
		$this->logger->notice('Received graceful shutdown signal. Starting shutting down');

		$timeToShutdown = $this->config->workerGracefulShutdownPeriod;
		$shutdownDeadline = time() + $timeToShutdown;
		while ($this->hasRunningChildren())
		{
			if (time() > $shutdownDeadline)
			{
				$this->logger->error(
					'Could not finish all children in a given deadline of {timeToShutdown} seconds.'
					. ' Stop shutting down and exit immediately',
					[
						'timeToShutdown' => $timeToShutdown,
					]
				);

				break;
			}

			// send shutdown signal to all children
			foreach ($this->children as $pids)
			{
				foreach ($pids as $pid)
				{
					$this->shutdownChild($pid);
				}
			}

			// wait a bit - let them finish their current job
			sleep($this->config->waitWorkerExitPeriod);

			// check which children has exited
			foreach (array_keys($this->children) as $queueName)
			{
				$this->actualizeChildrenList($queueName);
			}
		}

		$this->logger->notice('Shutdown complete. Bye!');

		die();
	}

	private function shutdownChild(int $pid): void
	{
		$this->logger->info('Shutting down child {childPid}', ['childPid' => $pid]);

		if (!isset($this->gracefulShutdownSignalSent[$pid]) && Signal::sendGracefulShutdownSignal($pid))
		{
			$this->logger->debug('Sent graceful termination signal to child {childPid}', ['childPid' => $pid]);

			$this->gracefulShutdownSignalSent[$pid] = $pid;

			return;
		}

		if (!isset($this->terminationSent[$pid]) && Signal::sendTerminationSignal($pid))
		{
			$this->logger->debug('Sent termination signal to child {childPid}', ['childPid' => $pid]);

			$this->terminationSent[$pid] = $pid;

			return;
		}

		if (Signal::sendKillSignal($pid))
		{
			$this->logger->debug('Sent kill (SIGKILL) signal to child {childPid}', ['childPid' => $pid]);
		}
		else
		{
			$this->logger->error('Error trying to shutdown child {childPid}', ['childPid' => $pid]);
		}
	}
}
