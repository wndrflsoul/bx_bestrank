<?php

namespace Bitrix\TransformerController\Runner;

abstract class Runner
{
	/**
	 * Method to execute some command.
	 *
	 * @param string $command Command to execute.
	 * @return mixed
	 */
	abstract public function run($command);
}