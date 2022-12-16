<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\Promise;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

use function Amp\call;

final class AlwaysPublishesMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Queue $queue,
    ) {
    }

    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            $promise = $this->publisher->publish($this->queue, $message);
            $promise->onResolve(fn () => $next($message));

            return $promise;
        });
    }
}
