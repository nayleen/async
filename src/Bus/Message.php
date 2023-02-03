<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus;

/**
 * @api
 */
interface Message
{
    public function name(): string;
}
