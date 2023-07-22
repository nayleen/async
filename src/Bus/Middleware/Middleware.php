<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use Nayleen\Async\Bus\Message;

interface Middleware
{
    /**
     * @param Closure(Message): void $next
     */
    public function handle(Message $message, Closure $next): void;
}
