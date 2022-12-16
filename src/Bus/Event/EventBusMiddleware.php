<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\Promise;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

use function Amp\call;

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
     * @param callable(Message): Promise $next
     * @return Promise<null>
     */
    public function handle(Message $message, callable $next): Promise
    {
        return call(function () use ($message, $next) {
            $handlers = $this->handlers->filter($message);

            if (count($handlers) === 0) {
                /** @var Promise<null> $promise */
                $promise = call($next, $message);

                return $promise;
            }

            $this->logger->log($this->level, 'Started notifying event handlers', ['event' => $message]);

            /** @var Promise<null>[] $promises */
            $promises = [];
            foreach ($handlers as $handler) {
                $promises[] = $handler($message);
            }

            /** @var Promise<null> $promise */
            $promise = Promise\all($promises);
            $promise->onResolve(function () use ($message, $next) {
                $this->logger->log($this->level, 'Finished notifying event handlers', ['event' => $message]);

                return call($next, $message);
            });

            return $promise;
        });
    }
}
