<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LoggingMiddleware implements Middleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $level = LogLevel::DEBUG,
    ) {
    }

    public function handle(Message $message, callable $next): void
    {
        $this->logger->log($this->level, 'Started handling message', ['message' => $message]);
        $next($message);
        $this->logger->log($this->level, 'Finished handling message', ['message' => $message]);
    }
}
