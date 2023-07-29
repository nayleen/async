<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use Nayleen\Async\Bus\Message;

/**
 * @internal
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
