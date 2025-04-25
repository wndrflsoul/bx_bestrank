<?php

namespace Bitrix\TransformerController\Runner;

use Bitrix\TransformerController\Document;
use Bitrix\TransformerController\Log;
use Psr\Log\LoggerInterface;

class SystemRunner extends Runner
{
	private LoggerInterface $logger;

	public function __construct()
	{
		$this->logger = Log::logger();
	}

	/**
	 * Call exec and return the result on success, false on failure.
	 *
	 * @param string $command Command to execute.
	 * @return bool|array
	 */
	public function run($command)
	{
		if (!self::isCommandAllowed($command))
		{
			$this->logger->critical(
				'Command execution is aborted since it contains executables that are not allowed',
				[
					'type' => 'runner',
					'command' => $command,
					'pid' => getmypid(),
				]
			);

			return false;
		}

		$result = false;
		exec($command, $result);
		return $result;
	}

	private static function isCommandAllowed(string $command): bool
	{
		// split by && or || or | or ;
		$subCommands = array_map('trim', preg_split('#&&|\|\||;|\|#', $command));

		$allowedList = self::getAllowedColumnsList();
		foreach ($subCommands as $singleCommand)
		{
			$firstSpacePosition = strpos($singleCommand, ' ');
			if ($firstSpacePosition === false)
			{
				$executable = $singleCommand;
			}
			else
			{
				$executable = substr($singleCommand, 0, $firstSpacePosition);
			}

			if (!isset($allowedList[$executable]))
			{
				return false;
			}
		}

		return true;
	}

	private static function getAllowedColumnsList(): array
	{
		static $list = null;

		$list ??= [
			'pwd' => 'pwd', // in healthcheck in admin panel
			'file' => 'file', // file -b --mime-type
			Document::getLibreOfficePath() => Document::getLibreOfficePath(), // document transformation
			'ffmpeg' => 'ffmpeg', // video transformation
		];

		return $list;
	}
}
