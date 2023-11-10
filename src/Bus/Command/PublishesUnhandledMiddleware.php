<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Closure;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use OutOfBoundsException;

class PublishesUnhandledMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Queue $queue,
    ) {}

    public function handle(Message $message, Closure $next): void
    {
        try {
            $next($message);
        } catch (OutOfBoundsException) {
            $this->publisher->publish($this->queue, $message);
        }
    }
}
