<?php

namespace Bitrix\TransformerController\Daemon\Config;

use Bitrix\TransformerController\Daemon\Dto\Config;

final class Resolver
{
	private static Config $config;

	public static function getCurrent(): Config
	{
		return self::$config;
	}

	public static function setCurrent(Config $config): void
	{
		self::$config = $config;
	}

	public function resolve(string $configFilePath, ?int $maxWorkerProcesses = null): Config
	{
		$userConfig = $this->getUserConfig($configFilePath);
		$defaults = $this->getDefaults();

		$config = new Config();

		$this->fillRabbitmqSection($config, $userConfig, $defaults, $maxWorkerProcesses);
		$this->fillControllerSection($config, $userConfig, $defaults);
		$this->fillWorkerLifetimeSection($config, $userConfig, $defaults);
		$this->fillHttpSection($config, $userConfig, $defaults);
		$this->fillTransformationSection($config, $userConfig, $defaults);
		$this->fillFilesSection($config, $userConfig, $defaults);
		$this->fillLogSection($config, $userConfig, $defaults);

		return $config;
	}

	private function getUserConfig(string $path): array
	{
		if (!is_readable($path))
		{
			return [];
		}

		$json = file_get_contents($path);
		if (!$json)
		{
			return [];
		}

		try
		{
			return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			return [];
		}
	}

	private function getDefaults(): array
	{
		$isVarLogTransformerWritable = is_writable('/var/log/transformer');

		return [
			'rabbitmq' => [
				'connection' => [
					'host' => '127.0.0.1',
					'port' => 5672,
					'vhost' => '/',
					'login' => 'guest',
					'password' => 'guest',
				],

				'queues' => [
					'main_preview' => [
						'workers' => 5,
					],
					'documentgenerator_create' => [
						'workers' => 5,
					],
				],

				'useAutoAck' => false,
			],
			'controller' => [
				'baseUrl' => 'http://127.0.0.1',
				'token' => 'example',
				'banListActualizationPeriod' => 15 * 60, // 15 minutes by default
			],
			'workerLifetime' => [
				'min' => 5 * 60,
				'max' => 10 * 60,
				'gracefulShutdownPeriod' => 9, // docker gives us 10 seconds to shut down
				'waitWorkerExitPeriod' => 2, // SIGUSR1 ... 2 seconds ... SIGTERM ... 2 seconds ... SIGKILL
			],
			'http' => [
				'timeouts' => [
					'default' => [
						'socket' => 8,
						'stream' => 30,
					],
					'uploadChunk' => [
						'socket' => 8,
						'stream' => 300,
					],
					'finish' => [
						'socket' => 4,
						'stream' => 300,
					],
				],
				'maxRedirects' => 10,
				'maxUploadChunkSize' => 10485760,
				'debug' => false,
				'privateIp' => true,
				'persistentConnections' => [
					'enabled' => true, // the same default as in curl
					'connectionMaxIdleTime' => 118, // the same default as in curl
					'connectionMaxLifeTime' => 0, // the same default as in curl - life of a connection is not limited if its not idle
					'maxAliveConnectionsPerWorker' => 5, // the same default as in curl
					'closeConnectionToClientAfterJobFinish' => false,
				],
			],
			'transformation' => [
				'libreoffice' => [
					'path' => 'libreoffice',
					'url' => 'http://127.0.0.1:9090/convert',
					'mode' => 'local', // or 'http'
					'saveFilesOnError' => false,
					'timeouts' => [
						0 => 60, // single timeout for all file sizes
					],
				],
				'ffmpeg' => [
					'path' => 'ffmpeg',
					'url' => 'http://127.0.0.1:9091/convert',
					'mode' => 'local', // or 'http'
					'saveFilesOnError' => false,
					'maxWidth' => 1280,
					'timeouts' => [
						1048576 => 180,
						10485760 => 300,
						104857600 => 600,
						304857600 => 900,
						1073741824 => 1800,
						3221225472 => 3600,
					],
				],
			],
			'files' => [
				'tmpDir' => $_SERVER['DOCUMENT_ROOT'] . '/upload/transformercontroller',
				'errorFilesDir' => $_SERVER['DOCUMENT_ROOT'] . '/upload/transformercontroller/error',
			],
			'log' => [
				'files' => [
					'default' => $isVarLogTransformerWritable
						? '/var/log/transformer/transformer.log'
						: $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/transformercontroller.log'
					,
					'ffmpeg' => $isVarLogTransformerWritable ? '/var/log/transformer/ffmpeg.log' : null,
					'http' => $isVarLogTransformerWritable ? '/var/log/transformer/http.log' : null,
				],
				'level' => 'info',
				'responseMaxLength' => 200,
			],
		];
	}

	private function fillRabbitmqSection(Config $config, array $userConfig, array $defaultConfig, ?int $maxWorkerProcesses): void
	{
		$config->rabbitmqHost = $userConfig['rabbitmq']['connection']['host'] ?? $defaultConfig['rabbitmq']['connection']['host'];
		$config->rabbitmqPort = $userConfig['rabbitmq']['connection']['port'] ?? $defaultConfig['rabbitmq']['connection']['port'];
		$config->rabbitmqVHost = $userConfig['rabbitmq']['connection']['vhost'] ?? $defaultConfig['rabbitmq']['connection']['vhost'];
		$config->rabbitmqLogin = $userConfig['rabbitmq']['connection']['login'] ?? $defaultConfig['rabbitmq']['connection']['login'];
		$config->rabbitmqPassword = $userConfig['rabbitmq']['connection']['password'] ?? $defaultConfig['rabbitmq']['connection']['password'];
		$config->isUseAutoAck = $userConfig['rabbitmq']['useAutoAck'] ?? $defaultConfig['rabbitmq']['useAutoAck'];

		$queues = $userConfig['rabbitmq']['queues'] ?? $defaultConfig['rabbitmq']['queues'];

		$config->queueWorkers = $this->getWorkersNumbers($queues, $maxWorkerProcesses);
	}

	private function fillControllerSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->controllerBaseUrl = $userConfig['controller']['baseUrl'] ?? $defaultConfig['controller']['baseUrl'];
		$config->controllerToken = $userConfig['controller']['token'] ?? $defaultConfig['controller']['token'];
		$config->banListActualizationPeriod = $userConfig['controller']['banListActualizationPeriod'] ?? $defaultConfig['controller']['banListActualizationPeriod'];
	}

	private function getWorkersNumbers(array $queuesConfig, ?int $maxWorkerProcesses): array
	{
		$queueToWorkersNumberMap = [];
		$totalWorkers = 0;
		foreach ($queuesConfig as $name => $queueConfig)
		{
			$queueToWorkersNumberMap[$name] = $queueConfig['workers'];
			$totalWorkers += $queueConfig['workers'];
		}

		// recalculate processes proportionally to max allowed number
		if ($totalWorkers > 0 && $maxWorkerProcesses > 0 && $totalWorkers !== $maxWorkerProcesses)
		{
			$ratio = $maxWorkerProcesses / $totalWorkers;
			foreach ($queueToWorkersNumberMap as $queue => $workers)
			{
				if ($workers > 0)
				{
					$workers = round($workers * $ratio);
					if ($workers <= 0)
					{
						$workers = 1;
					}
				}

				$queueToWorkersNumberMap[$queue] = $workers;
			}
		}

		return $queueToWorkersNumberMap;
	}

	private function fillWorkerLifetimeSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->workerMinLifetime = $userConfig['workerLifetime']['min'] ?? $defaultConfig['workerLifetime']['min'];
		$config->workerMaxLifetime = $userConfig['workerLifetime']['max'] ?? $defaultConfig['workerLifetime']['max'];
		$config->workerGracefulShutdownPeriod = $userConfig['workerLifetime']['gracefulShutdownPeriod'] ?? $defaultConfig['workerLifetime']['gracefulShutdownPeriod'];
		$config->waitWorkerExitPeriod = $userConfig['workerLifetime']['waitWorkerExitPeriod'] ?? $defaultConfig['workerLifetime']['waitWorkerExitPeriod'];
	}

	private function fillHttpSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->defaultSocketTimeout = $userConfig['http']['timeouts']['default']['socket'] ?? $defaultConfig['http']['timeouts']['default']['socket'];
		$config->defaultStreamTimeout = $userConfig['http']['timeouts']['default']['stream'] ?? $defaultConfig['http']['timeouts']['default']['stream'];
		$config->uploadChunkSocketTimeout = $userConfig['http']['timeouts']['uploadChunk']['socket'] ?? $defaultConfig['http']['timeouts']['uploadChunk']['socket'];
		$config->uploadChunkStreamTimeout = $userConfig['http']['timeouts']['uploadChunk']['stream'] ?? $defaultConfig['http']['timeouts']['uploadChunk']['stream'];
		$config->finishSocketTimeout = $userConfig['http']['timeouts']['finish']['socket'] ?? $defaultConfig['http']['timeouts']['finish']['socket'];
		$config->finishStreamTimeout = $userConfig['http']['timeouts']['finish']['stream'] ?? $defaultConfig['http']['timeouts']['finish']['stream'];
		$config->maxRedirects = $userConfig['http']['maxRedirects'] ?? $defaultConfig['http']['maxRedirects'];
		$config->maxUploadChunkSize = $userConfig['http']['maxUploadChunkSize'] ?? $defaultConfig['http']['maxUploadChunkSize'];
		$config->isWriteHttpDebugLog = $userConfig['http']['debug'] ?? $defaultConfig['http']['debug'];
		$config->isPrivateIpAllowed = $userConfig['http']['privateIp'] ?? $defaultConfig['http']['privateIp'];

		// persistent connections
		$config->isKeepConnectionAlive = $userConfig['http']['persistentConnections']['enabled'] ?? $defaultConfig['http']['persistentConnections']['enabled'];
		$config->connectionMaxIdleTime = $userConfig['http']['persistentConnections']['connectionMaxIdleTime'] ?? $defaultConfig['http']['persistentConnections']['connectionMaxIdleTime'];
		$config->connectionMaxLifeTime = $userConfig['http']['persistentConnections']['connectionMaxLifeTime'] ?? $defaultConfig['http']['persistentConnections']['connectionMaxLifeTime'];
		$config->maxAliveConnections = $userConfig['http']['persistentConnections']['maxAliveConnectionsPerWorker'] ?? $defaultConfig['http']['persistentConnections']['maxAliveConnectionsPerWorker'];
		$config->isCloseConnectionToClientAfterJobFinish = $userConfig['http']['persistentConnections']['closeConnectionToClientAfterJobFinish'] ?? $defaultConfig['http']['persistentConnections']['closeConnectionToClientAfterJobFinish'];
	}

	private function fillTransformationSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->libreofficePath = $userConfig['transformation']['libreoffice']['path'] ?? $defaultConfig['transformation']['libreoffice']['path'];
		$config->libreofficeUrl = $userConfig['transformation']['libreoffice']['url'] ?? $defaultConfig['transformation']['libreoffice']['url'];
		$config->isSaveFilesOnLibreofficeError = $userConfig['transformation']['libreoffice']['saveFilesOnError'] ?? $defaultConfig['transformation']['libreoffice']['saveFilesOnError'];
		$config->libreofficeTimeouts = $this->normalizeTransformationTimeouts(
			$userConfig['transformation']['libreoffice']['timeouts'] ?? [],
			$defaultConfig['transformation']['libreoffice']['timeouts'],
		);

		$libreofficeMode = $userConfig['transformation']['libreoffice']['mode'] ?? $defaultConfig['transformation']['libreoffice']['mode'];
		$config->isUseHttpForLibreoffice = $libreofficeMode === 'http';

		$config->ffmpegPath = $userConfig['transformation']['ffmpeg']['path'] ?? $defaultConfig['transformation']['ffmpeg']['path'];
		$config->ffmpegUrl = $userConfig['transformation']['ffmpeg']['url'] ?? $defaultConfig['transformation']['ffmpeg']['url'];
		$config->isSaveFilesOnFfmpegError = $userConfig['transformation']['ffmpeg']['saveFilesOnError'] ?? $defaultConfig['transformation']['ffmpeg']['saveFilesOnError'];
		$config->ffmpegTimeouts = $this->normalizeTransformationTimeouts(
			$userConfig['transformation']['ffmpeg']['timeouts'] ?? [],
			$defaultConfig['transformation']['ffmpeg']['timeouts'],
		);
		$config->ffmpegMaxWidth = $userConfig['transformation']['ffmpeg']['maxWidth'] ?? $defaultConfig['transformation']['ffmpeg']['maxWidth'];

		$ffmpegMode = $userConfig['transformation']['ffmpeg']['mode'] ?? $defaultConfig['transformation']['ffmpeg']['mode'];
		$config->isUseHttpForFfmpeg = $ffmpegMode === 'http';
	}

	private function normalizeTransformationTimeouts(array $userTimeouts, array $defaultTimeouts): array
	{
		$normalizedUserTimeouts = array_filter(array_map('intval', $userTimeouts), fn(int $timeout) => $timeout > 0);

		$timeouts = $normalizedUserTimeouts ?: $defaultTimeouts;

		ksort($timeouts);

		return $timeouts;
	}

	private function fillFilesSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->tmpFilesDir = $userConfig['files']['tmpDir'] ?? $defaultConfig['files']['tmpDir'];
		$config->errorFilesDir = $userConfig['files']['errorFilesDir'] ?? $defaultConfig['files']['errorFilesDir'];
	}

	private function fillLogSection(Config $config, array $userConfig, array $defaultConfig): void
	{
		$config->logFilePath = $userConfig['log']['files']['default'] ?? $defaultConfig['log']['files']['default'];
		$config->httpLogFilePath = $userConfig['log']['files']['http'] ?? $defaultConfig['log']['files']['http'] ?? $config->logFilePath;
		$config->logLevel = $userConfig['log']['level'] ?? $defaultConfig['log']['level'];
		$config->responseMaxLengthInLogs = $userConfig['log']['responseMaxLength'] ?? $defaultConfig['log']['responseMaxLength'];
	}
}
