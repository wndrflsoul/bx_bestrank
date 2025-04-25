<?php

namespace Bitrix\TransformerController\Daemon\Error;

final class Dictionary
{
	public const WRONG_STATUS_BEFORE_DOWNLOAD = 100;
	public const WRONG_CONTENT_TYPE_BEFORE_DOWNLOAD = 101;
	public const FILE_IS_TOO_BIG_ON_DOWNLOAD = 102;
	public const DOMAIN_IS_BANNED = 103;
	public const QUEUE_ADD_EVENT = 150;
	public const QUEUE_ADD_FAIL = 151;
	public const QUEUE_NOT_FOUND = 152;
	public const MODULE_NOT_INSTALLED = 153;
	public const RIGHT_CHECK_FAILED = 154;
	public const LIMIT_EXCEEDED = 155;
	public const WRONG_STATUS_AFTER_DOWNLOAD = 200;
	public const CANT_DOWNLOAD_FILE = 201;
	public const FILE_IS_TOO_BIG_AFTER_DOWNLOAD = 202;
	public const UPLOAD_FILES = 203;
	public const TRANSFORMATION_FAILED = 300;
	public const COMMAND_FAILED = 301;
	public const COMMAND_NOT_FOUND = 302;
	public const COMMAND_ERROR = 303;
	public const TRANSFORMATION_TIMED_OUT = 304;
}
