<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus;

/**
 * @api
 */
interface Bus
{
    public function handle(Message $message): void;
}
