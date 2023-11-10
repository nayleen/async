<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Closure;
use Monolog\Level;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class EventBusMiddleware implements Middleware
{
    public function __construct(
        private readonly EventHandlers $handlers,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly int|Level|string $level = LogLevel::DEBUG,
    ) {}

    /**
     * @param Closure(Message): void $next
     */
    public function handle(Message $message, Closure $next): void
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
