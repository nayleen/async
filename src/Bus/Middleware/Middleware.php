<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\Promise;
use Nayleen\Async\Bus\Message;

interface Middleware
{
    /**
     * @param callable(Message): Promise $next
     */
    public function handle(Message $message, callable $next): Promise;
}
