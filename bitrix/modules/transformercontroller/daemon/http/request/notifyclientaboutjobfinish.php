<?php

namespace Bitrix\TransformerController\Daemon\Http\Request;

use Bitrix\TransformerController\Daemon\Config\Resolver;
use Bitrix\TransformerController\Daemon\Error;
use Bitrix\TransformerController\Daemon\Http\Request;
use Bitrix\TransformerController\Daemon\Http\Response;
use Bitrix\TransformerController\Daemon\Http\Utils;
use Bitrix\TransformerController\Daemon\Result;
use Psr\Http\Client\ClientExceptionInterface;

class NotifyClientAboutJobFinish extends Request
{
	public function __construct(
		private readonly string $backUrl,
		private readonly ?array $result,
		private readonly ?Error $error,
	)
	{
		parent::__construct();
	}

	public function send(): Result
	{
		$config = Resolver::getCurrent();

		$options = [
			'form' => $this->prepareForm(),
			'socketTimeout' => $config->finishSocketTimeout,
			'streamTimeout' => $config->finishStreamTimeout,
		];
		if ($config->isCloseConnectionToClientAfterJobFinish)
		{
			// close open connection after this request
			$options['headers'] = [
				'Connection' => 'close',
			];
		}

		try
		{
			$rawResponse = $this->factory->getClient()->post(
				$this->backUrl,
				$options,
			);
		}
		catch (ClientExceptionInterface $exception)
		{
			$this->logger->error(
				'Error while notifying client about job finish: {exceptionMessage}',
				[
					'exceptionMessage' => $exception->getMessage(),
					'exceptionCode' => $exception->getCode(),
					'backUrl' => $this->backUrl,
					'result' => $this->result,
					'error' => $this->error,
				]
			);

			return (new Result())->addError(new Error('Failed notifying client about job finish'));
		}

		$content = Utils::getBodyString($rawResponse);

		if ($rawResponse->getStatusCode() !== 200)
		{
			$message = "Wrong http status-code {$rawResponse->getStatusCode()} from back_url"
				. ' while notifying client portal about job finish'
			;

			$this->logger->error(
				$message,
				[
					'backUrl' => $this->backUrl,
					'result' => $this->result,
					'error' => $this->error,
					'statusCode' => $rawResponse->getStatusCode(),
					'response' => Utils::cutResponse($content),
				]
			);

			return (new Result())->addError(new Error($message));
		}

		return (new Result())->setDataKey('content', $content);
	}

	private function prepareForm(): array
	{
		$formData = [
			'finish' => 'y',
		];

		if ($this->result)
		{
			$formData['result'] = $this->result;
		}

		if ($this->error)
		{
			$formData['error'] = $this->error->getMessage();
			$formData['errorCode'] = $this->error->getCode();
		}

		return $formData;
	}
}
