<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Closure;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

class AlwaysPublishesMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Queue $queue,
    ) {}

    public function handle(Message $message, Closure $next): void
    {
        $this->publisher->publish($this->queue, $message);
        $next($message);
    }
}
