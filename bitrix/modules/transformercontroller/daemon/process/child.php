<?php

namespace Bitrix\TransformerController\Daemon\Process;

use Bitrix\TransformerController\Daemon\BanList;
use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Dto\Job;
use Bitrix\TransformerController\Daemon\File\DeleteQueue;
use Bitrix\TransformerController\Daemon\JobProcessor;
use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Process\Child\Lifetime;
use Bitrix\TransformerController\Daemon\Transformation\ConverterRegistry;
use Psr\Log\LoggerInterface;

class Child
{
	public function __construct(
		private readonly string $queueName,
		private readonly Config $config,
		private readonly BanList $banList,
		private readonly LoggerInterface $logger,
	)
	{
	}

	public function start(): never
	{
		$lifetime = new Lifetime($this->config);

		$this->bootstrap($lifetime);

		$queue = new \Bitrix\TransformerController\Daemon\Queue($this->queueName, $this->config, $this->logger);
		$queue->connect();

		$this->logger->info(
			'Worker for queue {queue} has started at {date} end time is {endDate} (lifetime {lifetime} seconds)',
			[
				'queue' => $this->queueName,
				'date' => date('Y-m-d H:i:s'),
				'endDate' => date('Y-m-d H:i:s', $lifetime->getScheduledDieTime()),
				'lifetime' => $lifetime->getPlannedLifeTime(),
			]
		);

		$jobProcessor = new JobProcessor(
			$this->logger,
			$this->banList,
			LoggerFactory::getInstance(),
			new ConverterRegistry(),
		);

		$queue->consume(function (Job $job) use ($lifetime, $jobProcessor): void {
			Signal::processPendingSignals();

			$jobProcessor->process($job);

			Signal::processPendingSignals();

			if ($lifetime->isTimeToDie())
			{
				$lifetime->die();
			}
		});

		throw new \RuntimeException('This line should be unreachable. Something went wrong if you see this');
	}

	private function bootstrap(Lifetime $lifetime): void
	{
		DeleteQueue::getInstance()->setLogger($this->logger);

		// replace parent signal handler
		// ATTENTION! after signal has arrived, signal handler will be called
		// only when '$queue->consume` handler is called
		Signal::subscribeToGracefulShutdownSignal($lifetime->scheduleDieAfterThisJobFinish(...));

		// probably if we receive SIGTERM, it means that child has stuck on 'consume' call and no jobs arrive and this
		// handler will never be called. but add this handler just in case
		Signal::subscribeToTerminationSignal($lifetime->cleanupBeforeDying(...));

		// in case of unhandled error
		register_shutdown_function($lifetime->cleanupBeforeDying(...));
	}
}
