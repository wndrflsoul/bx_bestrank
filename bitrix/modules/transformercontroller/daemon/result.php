<?php

namespace Bitrix\TransformerController\Daemon;

final class Result implements \JsonSerializable
{
	private bool $isSuccess = true;

	private array $errors = [];

	private array $data = [];

	public function isSuccess(): bool
	{
		return $this->isSuccess;
	}

	public function addError(Error $error): self
	{
		$this->isSuccess = false;
		$this->errors[] = $error;
		return $this;
	}

	/**
	 * Returns an array of Error objects.
	 *
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Returns array of strings with error messages
	 *
	 * @return string[]
	 */
	public function getErrorMessages(): array
	{
		$messages = [];

		foreach ($this->getErrors() as $error)
		{
			$messages[] = $error->getMessage();
		}

		return $messages;
	}

	/**
	 * Adds array of Error objects
	 *
	 * @param Error[] $errors
	 * @return $this
	 */
	public function addErrors(array $errors): self
	{
		foreach ($errors as $error)
		{
			$this->addError($error);
		}

		return $this;
	}

	public function setData(array $data): self
	{
		$this->data = $data;

		return $this;
	}

	public function setDataKey(string $key, mixed $value): self
	{
		$this->data[$key] = $value;

		return $this;
	}

	/**
	 * Returns data array saved into the result.
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	public function getDataKey(string $key): mixed
	{
		return $this->data[$key] ?? null;
	}

	/**
	 * Used only for debug output
	 */
	public function jsonSerialize(): array
	{
		return [
			'success' => $this->isSuccess,
			'data' => $this->data,
			'errors' => $this->errors,
		];
	}
}
