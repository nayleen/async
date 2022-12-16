<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\Promise;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Bus\Queue\QueueMap;
use OutOfBoundsException;

use function Amp\call;

final class PublishesToConfiguredQueueMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly QueueMap $queueMap,
        private readonly ?Queue $fallback = null,
    ) {
    }

    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            try {
                $queue = $this->queueMap->queue($message);
            } catch (OutOfBoundsException $ex) {
                if (!$this->fallback) {
                    throw $ex;
                }

                $queue = $this->fallback;
            }

            $promise = $this->publisher->publish($queue, $message);
            $promise->onResolve(fn () => $next($message));

            return $promise;
        });
    }
}
