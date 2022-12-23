<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class CommandBusMiddleware implements Middleware
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
        $handler = $this->handlers->find($message);

        $this->logger->log($this->level, 'Started executing command handler', ['command' => $message]);
        $handler($message);
        $this->logger->log($this->level, 'Finished executing command handler', ['command' => $message]);

        $next($message);
    }
}
