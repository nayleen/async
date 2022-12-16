<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\Promise;
use Amp\Success;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use Throwable;

use function Amp\call;

final class PublishesOnErrorMiddleware implements Middleware
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Queue $queue,
        private readonly bool $rethrow = true,
    ) {
    }

    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            try {
                return $next($message);
            } catch (Throwable $ex) {
                yield $this->publisher->publish($this->queue, $message);

                if ($this->rethrow) {
                    throw $ex;
                }
            }

            return new Success();
        });
    }
}
