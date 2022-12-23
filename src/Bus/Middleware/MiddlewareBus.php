<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use DomainException;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

final class MiddlewareBus implements Bus
{
    /**
     * @var Middleware[]
     */
    private array $middlewares = [];

    /**
     * @var null|callable(Message): void
     */
    private mixed $stack = null;

    public function __construct(Middleware ...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->append($middleware);
        }
    }

    /**
     * @return callable(Message): void
     */
    private function createStack(int $index = 0): callable
    {
        if (!isset($this->middlewares[$index])) {
            return static fn (Message $message) => null;
        }

        return fn (Message $message) => $this->middlewares[$index]->handle($message, $this->createStack($index + 1));
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
