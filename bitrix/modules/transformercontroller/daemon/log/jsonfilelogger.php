<?php

namespace Bitrix\TransformerController\Daemon\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

final class JsonFileLogger extends AbstractLogger
{
	private const SUPPORTED_LEVELS = [
		LogLevel::EMERGENCY => LOG_EMERG,
		LogLevel::ALERT => LOG_ALERT,
		LogLevel::CRITICAL => LOG_CRIT,
		LogLevel::ERROR => LOG_ERR,
		LogLevel::WARNING => LOG_WARNING,
		LogLevel::NOTICE => LOG_NOTICE,
		LogLevel::INFO => LOG_INFO,
		LogLevel::DEBUG => LOG_DEBUG,
	];

	public function __construct(
		private readonly string $filePath,
		private readonly string $level = LogLevel::DEBUG,
		private readonly array $globalContext = [],
	)
	{
		if (!isset(self::SUPPORTED_LEVELS[$this->level]))
		{
			throw new \InvalidArgumentException(
				'Undefined log level. Allowed levels are ' . implode(', ', array_keys(self::SUPPORTED_LEVELS))
			);
		}
	}

	public function log($level, string|\Stringable $message, array $context = []): void
	{
		if (self::SUPPORTED_LEVELS[$level] > $this->level)
		{
			// shouldn't log anything because of the maximum verbose level
			return;
		}

		$allContext = [
			...$this->globalContext,
			...$context,
		];

		$formattedMessage = $this->formatMessage($message, $allContext);

		$timestamp = microtime(true);

		$json = [
			'message' => $formattedMessage,
			'level' => $level,
			'pid' => getmypid(),
			'date' => date('Y-m-d H:i:s', (int)$timestamp),
			'timestamp' => (int)$timestamp,
			'microTimestamp' => $timestamp,
		];

		$json += array_map($this->jsonify(...), $allContext);

		$this->write($json);
	}

	/**
	 * @param \Stringable|string $message
	 * @param string[] $context
	 *
	 * @return string
	 */
	private function formatMessage(\Stringable|string $message, array $context = []): string
	{
		// Implementors MAY have special handling for the passed objects. If that is not the case, implementors MUST cast it to a string.
		$message = $this->castToString($message);

		// Placeholder names MUST be delimited with a single opening brace { and a single closing brace }. There MUST NOT be any whitespace between the delimiters and the placeholder name.
		$replace = [];
		foreach ($context as $key => $val)
		{
			$replace['{' . $key . '}'] = $this->castToString($val);
		}

		return strtr($message, $replace);
	}

	private function castToString(mixed $value): string
	{
		if ($value instanceof \Stringable)
		{
			return (string)$value;
		}
		elseif (is_object($value) || is_array($value))
		{
			return var_export($value, true);
		}
		else
		{
			return (string)$value;
		}
	}

	private function jsonify(mixed $value): mixed
	{
		if (is_object($value) && !($value instanceof \JsonSerializable) && $value instanceof \Stringable)
		{
			return (string)$value;
		}

		return $value;
	}

	private function write(array $json): void
	{
		try
		{
			$encoded = json_encode($json, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $exception)
		{
			$encoded = json_encode(
				[
					'message' => 'Could not encode log message to JSON',
					'level' => LogLevel::CRITICAL,
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'type' => 'logger',
				],
				JSON_THROW_ON_ERROR,
			);
		}

		file_put_contents(
			$this->filePath,
			$encoded . PHP_EOL,
			FILE_APPEND
		);
	}
}
