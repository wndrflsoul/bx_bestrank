<?php

namespace Bitrix\TransformerController\Daemon\Dto;

class Ban
{
	public string $domain;
	public bool $isPermanent;
	public ?int $dateEndTimestamp;
	public ?string $queueName;
}
