<?php

namespace Bitrix\TransformerController\Daemon\Traits;

trait Singleton
{
	private function __construct()
	{
	}

	private function __clone(): void
	{
	}

	public static function getInstance(): static
	{
		static $instance = null;

		$instance ??= new static();

		return $instance;
	}
}
