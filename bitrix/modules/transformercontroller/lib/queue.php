<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;
use Bitrix\TransformerController\Entity\QueueTable;

class Queue
{
	private $connection;
	private $exchange;
	private $internalQueue;

	const MODULE_ID = 'transformercontroller';

	/**
	 * Queue constructor.
	 * @param \AMQPExchange $exchange Exchange with established connection.
	 * @param \AMQPQueue $queue Real queue we are working with.
	 * @param string $queueName Name of the queue.
	 * @throws InvalidOperationException
	 */
	public function __construct(\AMQPExchange $exchange, \AMQPQueue $queue, $queueName)
	{
		$this->connection = $exchange->getConnection();
		if(!$this->connection->isConnected())
		{
			throw new InvalidOperationException('Connection should be established');
		}
		$this->exchange = $exchange;
		$this->internalQueue = $queue;

		$this->internalQueue->setName($queueName);
		$this->internalQueue->setFlags(AMQP_DURABLE);
		$this->internalQueue->declareQueue();
		$this->internalQueue->bind($this->exchange->getName());
	}

	public function __destruct()
	{
		$this->connection->disconnect();
	}

	/**
	 * Create connection and exchange using module setting. Binds them and return exchange.
	 *
	 * @param string $exchangeName Name of the exchange.
	 * @return \AMQPExchange
	 * @throws ArgumentNullException
	 */
	public static function createExchange($exchangeName)
	{
		if(empty($exchangeName))
		{
			throw new ArgumentNullException('exchangeName');
		}
		$connectionParams = array(
			'login' => Option::get(self::MODULE_ID, 'login'),
			'password' => Option::get(self::MODULE_ID, 'password'),
			'host' => Option::get(self::MODULE_ID, 'host'),
			'port' => Option::get(self::MODULE_ID, 'port'),
			'vhost' => Option::get(self::MODULE_ID, 'vhost'),
		);
		// connect to queue
		$connection = new \AMQPConnection($connectionParams);
		$connection->connect();

		$channel = new \AMQPChannel($connection);
		$channel->setPrefetchCount(1);

		$exchange = new \AMQPExchange($channel);
		$exchange->setName($exchangeName);
		$exchange->setType(AMQP_EX_TYPE_DIRECT);
		$exchange->declareExchange();
		//$this->exchange->setFlags(AMQP_DURABLE);
		return $exchange;
	}

	/**
	 * Parse command and params, compose it into message and push to the real queue.
	 *
	 * @param string $className Class name to be called by worker.
	 * @param array $params Parameters of the command.
	 * @param array $usageInfo Some statistic Information.
	 * @return Result
	 */
	public function addMessage($className, $params = [], $usageInfo = [])
	{
		$result = new Result();
		$message = [
			'command' => $className,
			'params' => $params,
			'usageInfo' => $usageInfo,
		];
		$event = new Event('transformercontroller', 'onBeforeMessageAdd', [
			'message' => $message
		]);
		$event->send();
		foreach($event->getResults() as $eventResult)
		{
			$eventParams = $eventResult->getParameters();
			if($eventResult->getType() === EventResult::SUCCESS)
			{
				if(is_array($eventParams) && isset($eventParams['message']) && is_array($eventParams['message']))
				{
					$message = array_merge($message, $eventParams['message']);
				}
			}
			else
			{
				if(is_array($eventParams) || $eventParams instanceof \Traversable)
				{
					foreach($eventParams as $eventParam)
					{
						if($eventParam instanceof Error)
						{
							$result->addError($eventParam);
						}
					}
				}
				if($result->isSuccess())
				{
					$result->addError(new Error('Adding message to the queue was canceled by event', TimeStatistic::ERROR_CODE_QUEUE_ADD_EVENT));
				}
			}
		}
		if($result->isSuccess())
		{
			$message = $this->encode($message);
			try
			{
				$this->exchange->publish($message);
			}
			catch (\AMQPException)
			{
				$result->addError(new Error('Cant publish to queue', TimeStatistic::ERROR_CODE_QUEUE_ADD_FAIL));
			}
		}

		return $result;
	}

	/**
	 * Get next message, decode it and return array.
	 *
	 * If queue is empty returns false.
	 * @return bool|array
	 */
	public function getMessage()
	{
		if($envelope = $this->internalQueue->get(AMQP_AUTOACK))
		{
			$message = $this->decode($envelope->getBody());
			return $message;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Blocking method.
	 * Wait (forever) next message from the queue, decode it and call handler.
	 *
	 * @param callable $handler Function where message from queue will be passed.
	 * @return void
	 */
	public function processMessageWith($handler)
	{
		$consumeMethod = AMQP_NOPARAM;
		if(Settings::isUseAutoAck())
		{
			$consumeMethod = AMQP_AUTOACK;
		}
		$this->internalQueue->consume(function($envelope) use ($handler)
		{
			/* @var $envelope \AMQPEnvelope */
			$message = $this->decode($envelope->getBody());
			call_user_func($handler, $message['command'] ?? null, $message['params'] ?? null, $message['usageInfo'] ?? null, $envelope->getDeliveryTag());
		}, $consumeMethod);
	}

	/**
	 * Send acknowledgement of the message to the real queue.
	 *
	 * @param string $deliveryTag Internal queue message identifier.
	 * @return void
	 */
	public function deleteMessage($deliveryTag)
	{
		$this->internalQueue->ack($deliveryTag);
	}

	/**
	 * Check command and its parameters.
	 *
	 * @param string $className Class of the command, must extend BaseCommand.
	 * @param array $params Parameters to create command.
	 * @return Result
	 */
	public function checkCommand($className, $params)
	{
		$result = new Result();

		if(!is_a($className, BaseCommand::getClassName(), true))
		{
			$result->addError(new Error('class '.$className.' not found or it does not extend BaseCommand', TimeStatistic::ERROR_CODE_COMMAND_NOT_FOUND));
		}
		else
		{
			/* @var $className BaseCommand */
			$errors = $className::validate($params);
			if($errors)
			{
				$result->addErrors($errors);
			}
		}
		return $result;
	}

	/**
	 * @return ?string
	 */
	public function getName()
	{
		return $this->internalQueue->getName();
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getDefaultQueueName()
	{
		return QueueTable::getRow(['select' => ['NAME'], 'order' => ['SORT' => 'asc', 'ID' => 'asc']])['NAME'];
	}

	/**
	 * @param $id
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getQueueNameById($id)
	{
		return QueueTable::getRowById($id)['NAME'];
	}

	/**
	 * @param string $name
	 * @return bool|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getQueueIdByName($name)
	{
		if(empty($name))
		{
			return false;
		}
		$queue = QueueTable::getRow(['select' => ['ID'], 'filter' => ['NAME' => $name]]);
		if($queue && isset($queue['ID']))
		{
			return $queue['ID'];
		}

		return false;
	}

	/**
	 * @param $data
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function encode($data)
	{
		return Json::encode($data);
	}

	/**
	 * @param $message
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function decode($message)
	{
		return Json::decode($message);
	}
}
