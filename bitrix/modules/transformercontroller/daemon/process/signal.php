<?php

namespace Bitrix\TransformerController\Daemon\Process;

final class Signal
{
	private const GRACEFUL_SHUTDOWN = SIGUSR1;
	private const TERMINATION = SIGTERM;
	private const KILL = SIGKILL;

	public static function subscribeToGracefulShutdownSignal(callable $handler): void
	{
		pcntl_signal(self::GRACEFUL_SHUTDOWN, $handler);
	}

	public static function subscribeToTerminationSignal(callable $handler): void
	{
		pcntl_signal(self::TERMINATION, $handler);
	}

	public static function processPendingSignals(): void
	{
		pcntl_signal_dispatch();
	}

	public static function sendGracefulShutdownSignal(int $pid): bool
	{
		return self::sendSignal($pid, self::GRACEFUL_SHUTDOWN);
	}

	public static function sendTerminationSignal(int $pid): bool
	{
		return self::sendSignal($pid, self::TERMINATION);
	}

	public static function sendKillSignal(int $pid): bool
	{
		return self::sendSignal($pid, self::KILL);
	}

	private static function sendSignal(int $pid, int $signal): bool
	{
		$killResult = false;
		$exitCode = null;
		exec("kill -{$signal} {$pid}", $killResult, $exitCode);

		return $exitCode === 0;
	}
}
