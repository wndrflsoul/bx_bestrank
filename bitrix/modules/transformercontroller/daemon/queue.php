<?php

namespace Bitrix\TransformerController\Daemon;

use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Dto\Job;
use Psr\Log\LoggerInterface;

final class Queue
{
	private ?\AMQPExchange $exchange = null;
	private ?\AMQPQueue $queue = null;

	public function __construct(
		private readonly string $queueName,
		private readonly Config $config,
		private readonly LoggerInterface $logger,
	)
	{
	}

	public function __destruct()
	{
		$this->exchange?->getConnection()->disconnect();
		$this->queue?->getConnection()->disconnect();
	}

	public function connect(): void
	{
		$this->exchange = $this->createExchange($this->queueName);

		$this->queue = new \AMQPQueue($this->exchange->getChannel());
		$this->queue->setName($this->queueName);
		$this->queue->setFlags(AMQP_DURABLE);
		$this->queue->declareQueue();
		$this->queue->bind($this->exchange->getName());
	}

	private function createExchange(string $queueName): \AMQPExchange
	{
		if(empty($queueName))
		{
			throw new \InvalidArgumentException('queueName should not be empty');
		}

		$connectionParams = [
			'login' => $this->config->rabbitmqLogin,
			'password' => $this->config->rabbitmqPassword,
			'host' => $this->config->rabbitmqHost,
			'port' => $this->config->rabbitmqPort,
			'vhost' => $this->config->rabbitmqVHost,
		];
		// connect to queue
		$connection = new \AMQPConnection($connectionParams);
		$connection->connect();

		$channel = new \AMQPChannel($connection);
		$channel->setPrefetchCount(1);

		$exchange = new \AMQPExchange($channel);
		$exchange->setName($queueName);
		$exchange->setType(AMQP_EX_TYPE_DIRECT);
		$exchange->declareExchange();
		//$this->exchange->setFlags(AMQP_DURABLE);
		return $exchange;
	}

	/**
	 * Blocking method.
	 * Wait (forever) next message from the queue, decode it and call handler.
	 *
	 * @param callable $handler Function where message from queue will be passed.
	 * @return void
	 */
	public function consume(callable $handler): void
	{
		$consumeFlag = $this->config->isUseAutoAck ? AMQP_AUTOACK : AMQP_NOPARAM;

		$this->queue->consume(function (\AMQPEnvelope $envelope) use ($handler) {
			$decoded = $this->decode($envelope->getBody());
			if (empty($decoded))
			{
				return;
			}

			$this->logger->debug('Received message from rabbitmq', ['payload' => $decoded]);

			$job = $this->constructJob($decoded);
			if (!$job)
			{
				$this->logger->error(
					'Skipping message from rabbitmq since it has invalid structure',
					['payload' => $decoded],
				);

				return;
			}

			$this->queue->ack($envelope->getDeliveryTag());

			$handler($job);
		}, $consumeFlag);
	}

	private function decode(string $envelope): array
	{
		try
		{
			return json_decode($envelope, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			return [];
		}
	}

	private function constructJob(array $jsonPayload): ?Job
	{
		$job = new Job();

		if (empty($jsonPayload['command']) || !is_string($jsonPayload['command']))
		{
			return null;
		}
		$job->commandClass = $jsonPayload['command'];

		if (empty($jsonPayload['params']['formats']) || !is_array($jsonPayload['params']['formats']))
		{
			return null;
		}
		$job->formats = array_unique(array_filter(array_map('strval', $jsonPayload['params']['formats'])));

		if (!empty($jsonPayload['params']['file']) && is_string($jsonPayload['params']['file']))
		{
			$job->fileUrl = $jsonPayload['params']['file'];
		}
		else
		{
			$job->fileUrl = null;
		}

		if (empty($jsonPayload['usageInfo']['TARIF']) || !is_string($jsonPayload['usageInfo']['TARIF']))
		{
			return null;
		}
		$job->tarif = $jsonPayload['usageInfo']['TARIF'];

		if (empty($jsonPayload['params']['back_url']) || !is_string($jsonPayload['params']['back_url']))
		{
			return null;
		}
		$job->backUrl = $jsonPayload['params']['back_url'];

		if (empty($jsonPayload['usageInfo']['GUID']) || !is_string($jsonPayload['usageInfo']['GUID']))
		{
			return null;
		}
		$job->guid = $jsonPayload['usageInfo']['GUID'];

		if (empty($jsonPayload['usageInfo']['DOMAIN']) || !is_string($jsonPayload['usageInfo']['DOMAIN']))
		{
			return null;
		}
		$job->domain = $jsonPayload['usageInfo']['DOMAIN'];

		$job->queueName = $this->queueName;

		return $job;
	}
}
