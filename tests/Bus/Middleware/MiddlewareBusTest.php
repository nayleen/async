<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use DomainException;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
class MiddlewareBusTest extends AsyncTestCase
{
    private function createMiddleware(int $expectedIndex, array &$results = []): Middleware
    {
        return new class($results, $expectedIndex) implements Middleware {
            public function __construct(private array &$results, private int $expectedIndex)
            {
            }

            public function handle(Message $message, callable $next): void
            {
                $this->results[] = $this->expectedIndex;

                $next($message);
            }
        };
    }

    /**
     * @test
     */
    public function cannot_append_after_stack_has_been_created(): void
    {
        $this->expectException(DomainException::class);

        $bus = new MiddlewareBus();
        $bus->handle($this->createMock(Message::class));

        $bus->append($this->createMiddleware(1));
    }

    /**
     * @test
     */
    public function cannot_prepend_after_stack_has_been_created(): void
    {
        $this->expectException(DomainException::class);

        $bus = new MiddlewareBus();
        $bus->handle($this->createMock(Message::class));

        $bus->prepend($this->createMiddleware(1));
    }

    /**
     * @test
     */
    public function executes_middlewares_in_composed_order(): void
    {
        $results = [];

        $bus = new MiddlewareBus();
        $bus->append($this->createMiddleware(2, $results));
        $bus->append($this->createMiddleware(3, $results));
        $bus->prepend($this->createMiddleware(1, $results));

        $bus->handle($this->createMock(Message::class));

        self::assertSame([1, 2, 3], $results);
    }

    /**
     * @test
     */
    public function executes_middlewares_in_given_order(): void
    {
        $results = [];

        $bus = new MiddlewareBus(
            $this->createMiddleware(1, $results),
            $this->createMiddleware(2, $results),
        );
        $bus->handle($this->createMock(Message::class));

        self::assertSame([1, 2], $results);
    }
}
