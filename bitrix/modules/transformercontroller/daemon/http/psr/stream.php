<?php

namespace Bitrix\TransformerController\Daemon\Http\Psr;

use Psr\Http\Message\StreamInterface;

final class Stream implements StreamInterface
{
	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * @param string | resource | Stream $stream
	 * @param string $mode
	 * @throws \InvalidArgumentException
	 */
	public function __construct($stream, $mode = 'r')
	{
		if (is_resource($stream))
		{
			$this->resource = $stream;
		}
		elseif ($stream instanceof Stream)
		{
			$this->resource = $stream->resource;
		}
		elseif (is_string($stream))
		{
			$this->resource = fopen($stream, $mode);
			if (!is_resource($this->resource))
			{
				throw new \RuntimeException("Could not open stream with fopen (uri {$stream}, mode {$mode})");
			}
		}
		else
		{
			throw new \InvalidArgumentException('Stream must be a Stream object, a string identifier, or a resource.');
		}
	}

	public function __destruct()
	{
		// sometimes on destruct resource can be already closed even if we have a not-null handle
		if (is_resource($this->resource))
		{
			$this->close();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function __toString(): string
	{
		if (!$this->isReadable())
		{
			return '';
		}

		try
		{
			$this->rewind();
			return $this->getContents();
		}
		catch (\RuntimeException)
		{
			return '';
		}
	}

	/**
	 * @inheritdoc
	 */
	public function close(): void
	{
		if ($this->resource)
		{
			$resource = $this->detach();
			fclose($resource);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function detach()
	{
		$resource = $this->resource;
		$this->resource = null;
		return $resource;
	}

	/**
	 * @inheritdoc
	 */
	public function getSize(): ?int
	{
		if ($this->resource !== null)
		{
			$stats = fstat($this->resource);
			return $stats['size'];
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function tell(): int
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot tell position.');
		}

		$result = ftell($this->resource);
		if ($result === false)
		{
			throw new \RuntimeException('Error occurred during tell operation.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function eof(): bool
	{
		if ($this->resource)
		{
			return feof($this->resource);
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isSeekable(): bool
	{
		if ($this->resource)
		{
			return $this->getMetadata('seekable');
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function seek(int $offset, int $whence = SEEK_SET): void
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot seek position.');
		}

		if (!$this->isSeekable())
		{
			throw new \RuntimeException('Stream is not seekable.');
		}

		$result = fseek($this->resource, $offset, $whence);

		if ($result !== 0)
		{
			throw new \RuntimeException('Error seeking within stream.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function rewind(): void
	{
		$this->seek(0);
	}

	/**
	 * @inheritdoc
	 */
	public function isWritable(): bool
	{
		if ($this->resource)
		{
			$mode = $this->getMetadata('mode');

			if (str_contains($mode, '+'))
			{
				return true;
			}

			$modeWithoutBinary = str_replace('b', '', $mode);

			return in_array(
				$modeWithoutBinary,
				[
					'a',
					'w',
					'rw',
					'x',
					'c',
				]
			);
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function write(string $string): int
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot write.');
		}

		$result = fwrite($this->resource, $string);

		if ($result === false)
		{
			throw new \RuntimeException('Error writing to stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function isReadable(): bool
	{
		if ($this->resource)
		{
			$mode = $this->getMetadata('mode');

			return (str_contains($mode, 'r') || str_contains($mode, '+'));
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function read(int $length): string
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot read.');
		}

		if (!$this->isReadable())
		{
			throw new \RuntimeException('Stream is not readable.');
		}

		$result = fread($this->resource, $length);

		if ($result === false)
		{
			throw new \RuntimeException('Error reading stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getContents(): string
	{
		if (!$this->isReadable())
		{
			return '';
		}

		$result = stream_get_contents($this->resource);

		if ($result === false)
		{
			throw new \RuntimeException('Error reading stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getMetadata(?string $key = null)
	{
		if (!$this->resource)
		{
			return $key === null ? [] : null;
		}

		$meta = stream_get_meta_data($this->resource);

		if ($key === null)
		{
			return $meta;
		}

		return $meta[$key] ?? null;
	}
}
