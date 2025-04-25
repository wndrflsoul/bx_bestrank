<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main\Diag\Logger;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\HttpDebug;
use Bitrix\Main\Web\Json;
use Bitrix\TransformerController\Log\JsonLogFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class Log
{
	private static $forceDebug;

	public function __construct($forceDebug = false)
	{
		self::$forceDebug = $forceDebug;
	}

	public static function getPath($logName = 'transformer'): string
	{
		$dir = self::getLogRootDir();

		if ($dir)
		{
			return Path::combine($dir, $logName . '.log');
		}

		return Path::combine(
			Application::getDocumentRoot(),
			'bitrix',
			'modules',
			$logName . 'controller.log',
		);
	}

	private static function getLogRootDir(): ?string
	{
		$dir = null;
		if (defined('BX_TC_LOGS_ROOT_DIR'))
		{
			try
			{
				$dir = Path::normalize(BX_TC_LOGS_ROOT_DIR);
			}
			catch (InvalidPathException)
			{
			}
		}

		if ($dir && is_writable($dir))
		{
			return $dir;
		}

		if (is_writable('/var/log/transformer'))
		{
			return '/var/log/transformer';
		}

		return null;
	}

	private static function getMode(): bool
	{
		if(self::$forceDebug)
		{
			return true;
		}
		if(Option::get('transformercontroller', 'debug'))
		{
			return true;
		}

		return false;
	}

	private static function getLogLevel(): string
	{
		return (string)Option::get('transformercontroller', 'log_level', LogLevel::DEBUG);
	}

	private static function isHttpDebugEnabled(): bool
	{
		return Option::get('transformercontroller', 'http_debug_enabled', 'N') === 'Y';
	}

	private static function getHttpDebugLevel(): int
	{
		return (int)Option::get('transformercontroller', 'http_debug_level', HttpDebug::ALL);
	}

	/**
	 * @deprecated Use Log::logger() instead
	 *
	 * @param string|array $message Record to write.
	 * @return void
	 */
	public static function write($message)
	{
		$data = [
			'time' => date('d.m.Y H:i:s'),
			'pid' => getmypid(),
		];
		if(is_array($message))
		{
			$data = array_merge($data, $message);
		}
		else
		{
			$data['message'] = $message;
		}

		self::logger()->debug(Json::encode($data), $data);
	}

	/**
	 * clears log file.
	 * @return void
	 */
	public static function clear()
	{
		if(self::getMode())
		{
			@file_put_contents(self::getPath(), '');
		}
	}

	final public static function configureLogging(HttpClient $client): void
	{
		if (self::getMode() && self::isHttpDebugEnabled())
		{
			$logger = self::getHttpClientLogger();

			if ($logger)
			{
				$client->setLogger($logger);
				$client->setDebugLevel(self::getHttpDebugLevel());
			}
		}
	}

	private static function getHttpClientLogger(): ?LoggerInterface
	{
		$customLoggerFromSettings = Logger::create('transformercontroller.HttpClient');
		if ($customLoggerFromSettings)
		{
			return $customLoggerFromSettings;
		}

		// we don't want to create a new dir inside /bitrix/modules. so if there is no custom root, don't write logs at all
		if (self::getMode() && self::getLogRootDir())
		{
			$httpDir = Path::combine(self::getLogRootDir(), 'http');
			if (!Directory::isDirectoryExists($httpDir))
			{
				try
				{
					Directory::createDirectory($httpDir);
				}
				catch (\Throwable)
				{
				}
			}

			if (is_writable($httpDir))
			{
				// new file for each worker, it's easier to read this way
				$defaultLogger = new FileLogger(Path::combine($httpDir, getmypid() . '.log'));

				$defaultLogger->setLevel(self::getLogLevel());

				return $defaultLogger;
			}
		}

		return null;
	}

	final public static function logger(): LoggerInterface
	{
		$customLoggerFromSettings = Logger::create('transformercontroller.Default');
		if ($customLoggerFromSettings)
		{
			return $customLoggerFromSettings;
		}

		if (!self::getMode())
		{
			//logs are disabled
			return new NullLogger();
		}

		$defaultLogger = new FileLogger(
			self::getPath(),
			0, //dont rotate logs by default
		);

		$defaultLogger->setLevel(self::getLogLevel());
		$defaultLogger->setFormatter(new JsonLogFormatter(lineBreakAfterEachMessage: true));

		return $defaultLogger;
	}
}
