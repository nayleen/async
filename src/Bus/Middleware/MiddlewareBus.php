<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Closure;
use DomainException;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

/**
 * @api
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
            return static function (Message $message): void {
            };
        }

        return function (Message $message) use ($index): void {
            $this->middlewares[$index]->handle($message, $this->createStack($index + 1));
        };
    }

    public function append(Middleware $middleware): void
    {
        if (isset($this->stack)) {
            throw new DomainException();
        }

        $this->middlewares[] = $middleware;
    }

    public function handle(Message $message): void
    {
        if (!isset($this->stack)) {
            $this->stack = $this->createStack();
        }

        ($this->stack)($message);
    }

    public function prepend(Middleware $middleware): void
    {
        if (isset($this->stack)) {
            throw new DomainException();
        }

        array_unshift($this->middlewares, $middleware);
    }
}
