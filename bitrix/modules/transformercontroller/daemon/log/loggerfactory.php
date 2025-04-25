<?php

namespace Bitrix\TransformerController\Daemon\Log;

use Bitrix\TransformerController\Daemon\Dto\Config;
use Bitrix\TransformerController\Daemon\Traits\Singleton;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LoggerFactory
{
	use Singleton;

	public function create(Config $config, array $globalContext = []): LoggerInterface
	{
		return new JsonFileLogger($config->logFilePath, $config->logLevel, $globalContext);
	}

	public function createNullLogger(): LoggerInterface
	{
		return new NullLogger();
	}
}
