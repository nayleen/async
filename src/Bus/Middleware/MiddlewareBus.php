<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use LogicException;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

/**
 * @phpstan-consistent-constructor
 */
class MiddlewareBus implements Bus
{
    /**
     * @var Middleware[]
     */
    private array $middlewares = [];

    /**
     * @var Closure(Message): void
     */
    private Closure $stack;

    public function __construct(Middleware ...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->append($middleware);
        }
    }

    /**
     * @return Closure(Message): void
     */
    private function createStack(int $index = 0): Closure
    {
        if (!isset($this->middlewares[$index])) {
            return static function (Message $message): void {};
        }

        return function (Message $message) use ($index): void {
            $this->middlewares[$index]->handle($message, $this->createStack($index + 1));
        };
    }

    public function append(Middleware $middleware): void
    {
        assert(
            !isset($this->stack),
            new LogicException('Stack has been finalized'),
        );

        $this->middlewares[] = $middleware;
    }

    public function handle(Message $message): void
    {
        ($this->stack ??= $this->createStack())($message);
    }

    public function prepend(Middleware $middleware): void
    {
        assert(
            !isset($this->stack),
            new LogicException('Stack has been finalized'),
        );

        array_unshift($this->middlewares, $middleware);
    }
}
