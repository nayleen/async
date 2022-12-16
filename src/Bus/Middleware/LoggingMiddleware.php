<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\Promise;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use function Amp\call;

final class LoggingMiddleware implements Middleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $level = LogLevel::DEBUG,
    ) {
    }

    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            $this->logger->log($this->level, 'Started handling message', ['message' => $message]);

            /** @var Promise $promise */
            $promise = $next($message);
            $promise->onResolve(fn () => $this->logger->log($this->level, 'Finished handling message', ['message' => $message]));

            return $promise;
        });
    }
}
