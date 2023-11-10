<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Closure;
use Monolog\Level;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class CommandBusMiddleware implements Middleware
{
    public function __construct(
        private readonly CommandHandlers $handlers,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly int|Level|string $level = LogLevel::DEBUG,
    ) {}

    /**
     * @param Closure(Message): void $next
     */
    public function handle(Message $message, Closure $next): void
    {
        $handler = $this->handlers->find($message);

        $this->logger->log($this->level, 'Started executing command handler', ['command' => $message]);
        $handler($message);
        $this->logger->log($this->level, 'Finished executing command handler', ['command' => $message]);

        $next($message);
    }
}
