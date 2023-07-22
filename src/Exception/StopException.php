<?php

declare(strict_types = 1);

namespace Nayleen\Async\Exception;

use RuntimeException;

class StopException extends RuntimeException
{
    public function __construct(public readonly ?int $signal = null)
    {
        parent::__construct();
    }
}
