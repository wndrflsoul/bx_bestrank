<?php

namespace Bitrix\TransformerController;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Web\HttpClient;

/**
 * @internal
 */
final class Http
{
	/**
	 * @template T of array{0: HttpClient}
	 *
	 * @param string $scenario
	 * @param callable():T $sender
	 * @param callable(T):bool|null $checkAdditionallyIfShouldRetry
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function sendWithRetry(
		string $scenario,
		callable $sender,
		?callable $checkAdditionallyIfShouldRetry = null
	): array
	{
		$checkAdditionallyIfShouldRetry ??= fn(array $result) => true;

		$retryAttemptsCount = 0;
		$isFirstAttempt = true;
		do
		{
			if ($isFirstAttempt)
			{
				$isFirstAttempt = false;
			}
			else
			{
				$retryAttemptsCount++;
			}

			$result = $sender();
			$http = reset($result);
			if (!($http instanceof HttpClient))
			{
				throw new ArgumentTypeException('result', HttpClient::class);
			}

			// basically is 2xx
			$isSuccess = $http->getStatus() >= 200 && $http->getStatus() < 300;
			// basically not 4xx
			$isMayBeTemporaryProblem = $http->getStatus() < 400 || $http->getStatus() >= 500;
		} while (
			!$isSuccess
			&& $isMayBeTemporaryProblem
			&& Settings::getMaxConnectionRetryAttemptsCount($scenario) > $retryAttemptsCount
			&& $checkAdditionallyIfShouldRetry($result)
		);

		return $result;
	}
}
