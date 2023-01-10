<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Exception;

use LogicException;

final class NotRunningException extends LogicException
{
    protected $message = 'Kernel is not running.';
}
