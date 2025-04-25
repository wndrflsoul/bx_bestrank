<?php

namespace Bitrix\TransformerController\Daemon\Dto;

use Bitrix\TransformerController\Daemon\Error;

final class Statistic
{
	public ?int $fileSize = null; // can be null if there was no file_url in a job
	public ?Error $error = null;

	public int $startTimestamp;
	public int $endTimestamp;

	// all values below can be null if a corresponding step was not performed.
	// for example, there was no upload because there were no files in a result.
	public ?int $timeDownload = null;
	public ?int $timeExec = null;
	public ?int $timeUpload = null;
}
