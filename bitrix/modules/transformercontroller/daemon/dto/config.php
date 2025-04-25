<?php

namespace Bitrix\TransformerController\Daemon\Dto;

final class Config
{
	//region rabbitmq
	public string $rabbitmqHost;
	public int $rabbitmqPort;
	public string $rabbitmqVHost;
	public string $rabbitmqLogin;
	public string $rabbitmqPassword;
	/** @var Array<string, int> */
	public array $queueWorkers;
	public bool $isUseAutoAck;
	//endregion

	//region controller
	public string $controllerBaseUrl;
	public string $controllerToken;
	public int $banListActualizationPeriod;
	//endregion

	//region lifetime
	public int $workerMinLifetime;
	public int $workerMaxLifetime;
	public int $workerGracefulShutdownPeriod;
	public int $waitWorkerExitPeriod;
	//endregion

	//region http
	public int $defaultSocketTimeout;
	public int $defaultStreamTimeout;
	public int $uploadChunkSocketTimeout;
	public int $uploadChunkStreamTimeout;
	public int $finishSocketTimeout;
	public int $finishStreamTimeout;
	public int $maxRedirects;
	public int $maxUploadChunkSize;
	public bool $isWriteHttpDebugLog;
	public bool $isPrivateIpAllowed;
	public bool $isKeepConnectionAlive;
	public int $connectionMaxIdleTime;
	public int $connectionMaxLifeTime;
	public int $maxAliveConnections;
	public int $isCloseConnectionToClientAfterJobFinish;
	//endregion

	//region transformation
	public string $libreofficePath;
	public string $libreofficeUrl;
	public bool $isUseHttpForLibreoffice;
	public bool $isSaveFilesOnLibreofficeError;
	/** @var Array<int, int> - file size threshold to timeout in seconds */
	public array $libreofficeTimeouts;

	public string $ffmpegPath;
	public string $ffmpegUrl;
	public bool $isUseHttpForFfmpeg;
	public bool $isSaveFilesOnFfmpegError;
	/** @var Array<int, int> - file size threshold to timeout in seconds */
	public array $ffmpegTimeouts;
	public int $ffmpegMaxWidth;
	//endregion

	//region files
	public string $tmpFilesDir;
	public string $errorFilesDir;
	//endregion

	//region logs
	public string $logFilePath;
	public ?string $httpLogFilePath;
	public string $logLevel;
	public ?int $responseMaxLengthInLogs;
	//endregion

}
