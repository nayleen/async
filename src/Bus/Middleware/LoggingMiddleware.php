<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use Nayleen\Async\Bus\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @api
 */
class LoggingMiddleware implements Middleware
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $level = LogLevel::DEBUG,
    ) {
    }

    public function handle(Message $message, Closure $next): void
    {
        $this->logger->log($this->level, 'Started handling message', ['processed_message' => $message]);
        $next($message);
        $this->logger->log($this->level, 'Finished handling message', ['processed_message' => $message]);
    }
}
