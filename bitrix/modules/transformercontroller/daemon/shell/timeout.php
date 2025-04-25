<?php

namespace Bitrix\TransformerController\Daemon\Shell;

final class Timeout
{
	public static function wrapCommandInTimeout(string $command, int $timeout): string
	{
		return "timeout -k 10 {$timeout} $command";
	}

	/**
	 * Returns true if the exit code tells that a process was forcefully ended by 'timeout' utility
	 */
	public static function isTimeoutExitCode(int $exitCode): bool
	{
		return self::isTimeoutTerminateExitCode($exitCode) || self::isTimeoutKillExitCode($exitCode);
	}

	/**
	 * Returns true if the exit code tells that a process TERMINATED (SIGTERM) by 'timeout' utility
	 */
	public static function isTimeoutTerminateExitCode(int $exitCode): bool
	{
		return $exitCode === 124;
	}

	/**
	 * Returns true if the exit code tells that a process KILLED (SIGKILL) by 'timeout' utility
	 */
	public static function isTimeoutKillExitCode(int $exitCode): bool
	{
		return $exitCode === 137;
	}

	public static function chooseTimeout(int $fileSize, array $timeouts): int
	{
		$resultTimeout = reset($timeouts);

		foreach ($timeouts as $fileSizeThreshold => $singleTimeout)
		{
			if ($fileSize < $fileSizeThreshold)
			{
				return $resultTimeout;
			}

			$resultTimeout = $singleTimeout;
		}

		return $resultTimeout;
	}
}
