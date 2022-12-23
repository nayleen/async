<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use Throwable;

final class PublishesOnErrorMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Queue $queue,
        private readonly bool $rethrow = true,
    ) {
    }

    public function handle(Message $message, callable $next): void
    {
        try {
            $next($message);
        } catch (Throwable $ex) {
            $this->publisher->publish($this->queue, $message);

            if ($this->rethrow) {
                throw $ex;
            }
        }
    }
}
