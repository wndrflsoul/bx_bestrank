<?php

namespace Bitrix\TransformerController\Daemon\File;

use Bitrix\TransformerController\Daemon\Traits\Singleton;
use Psr\Log\LoggerAwareTrait;

final class DeleteQueue
{
	use Singleton;
	use LoggerAwareTrait;

	private array $queue = [];

	public function add(string $filePath): self
	{
		$this->queue[$filePath] = $filePath;

		return $this;
	}

	public function flush(): void
	{
		foreach ($this->queue as $path)
		{
			if (file_exists($path))
			{
				$this->logger?->debug('Deleting tmp file that we dont need anymore: {filePath}', ['filePath' => $path]);
				if (!unlink($path))
				{
					$this->logger?->error('Could not delete tmp file {filePath}', ['filePath' => $path]);
				}
			}
		}

		$this->queue = [];
	}
}
