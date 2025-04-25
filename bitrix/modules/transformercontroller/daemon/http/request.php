<?php

namespace Bitrix\TransformerController\Daemon\Http;

use Bitrix\TransformerController\Daemon\Log\LoggerFactory;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Request - business wrapper around raw http requests. Object implementing this interface should
 * prepare an HTTP request, send it and return a Result with business data and errors in it.
 */
abstract class Request implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	protected Factory $factory;

	public function __construct()
	{
		$this->factory = Factory::getInstance();
		$this->logger = LoggerFactory::getInstance()->createNullLogger();
	}

	public function setLoggerFluently(LoggerInterface $logger): self
	{
		$this->setLogger($logger);

		return $this;
	}

	/**
	 * Send the request and return result.
	 *
	 * This method SHOULD NOT throw an exception unless it's something fatal.
	 * This method SHOULD NOT return raw errors from http clients, dns resolves, curl, etc.
	 * They should be logged, but not returned.
	 *
	 * ALL ERRORS FROM THE RESULT WILL BE SENT TO A CLIENT.
	 * Therefore, all errors should have an appropriate message and code.
	 *
	 * @return Result
	 */
	abstract public function send(): Result;
}
