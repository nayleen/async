<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test\Bus\Middleware;

use Closure;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Middleware\Middleware;

/**
 * @psalm-internal Nayleen\Async
 */
final class TestMiddleware implements Middleware
{
    public function __construct(
        private readonly Results $results,
        private readonly int $expectedIndex,
    ) {
    }

    public function handle(Message $message, Closure $next): void
    {
        $this->results->list[] = $this->expectedIndex;
        $next($message);
    }
}
