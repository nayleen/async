<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Nayleen\Async\Bus\Message;

interface Middleware
{
    /**
     * @param callable(Message): void $next
     */
    public function handle(Message $message, callable $next): void;
}
