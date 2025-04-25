<?php

namespace Bitrix\TransformerController\Daemon\Error;

use Bitrix\TransformerController\Daemon\Error;

/**
 * This class represents an error that should not stop command execution. This is something like a warning.
 *
 * Not critical errors are not sent to the client, but they are recorded in stats.
 */
final class NotCritical extends Error
{
}
