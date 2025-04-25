<?php

namespace Bitrix\TransformerController\Daemon\Dto;

final class Job
{
	public string $commandClass;
	public array $formats;
	public ?string $fileUrl;
	public string $tarif;
	public string $backUrl;
	public string $guid;
	public string $domain;
	public string $queueName;
}
