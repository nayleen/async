<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class EventBusMiddleware implements Middleware
{
    private readonly string $level;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Handlers $handlers,
        ?LoggerInterface $logger = null,
        ?string $level = null,
    ) {
        $this->level = $level ?? LogLevel::DEBUG;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param callable(Message): void $next
     */
    public function handle(Message $message, callable $next): void
    {
        $handlers = $this->handlers->filter($message);

        if (count($handlers) === 0) {
            $next($message);
            return;
        }

        $this->logger->log($this->level, 'Started notifying event handlers', ['event' => $message]);

        foreach ($handlers as $handler) {
            $handler($message);
        }

        $this->logger->log($this->level, 'Finished notifying event handlers', ['event' => $message]);

        $next($message);
    }
}
