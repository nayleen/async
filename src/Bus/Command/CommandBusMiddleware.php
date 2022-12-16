<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\Promise;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

use function Amp\call;

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
     * @param callable(Message): Promise $next
     * @return Promise<null>
     */
    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            $handler = $this->handlers->find($message);

            $this->logger->log($this->level, 'Started executing command handler', ['command' => $message]);

            /** @var Promise<null> $promise */
            $promise = $handler($message);
            $promise->onResolve(function () use ($message, $next) {
                $this->logger->log($this->level, 'Finished executing command handler', ['command' => $message]);

                return call($next, $message);
            });

            return $promise;
        });
    }
}
