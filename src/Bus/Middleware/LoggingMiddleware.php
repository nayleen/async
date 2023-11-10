<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use Monolog\Level;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggingMiddleware implements Middleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly int|Level|string $level = LogLevel::DEBUG,
    ) {}

    public function handle(Message $message, Closure $next): void
    {
        try {
            $this->logger->log($this->level, 'Started handling message', ['message' => $message]);
            $next($message);
        } finally {
            $this->logger->log($this->level, 'Finished handling message', ['message' => $message]);
        }
    }
}
