<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Middleware;

use Amp\Promise;
use Amp\Success;
use DomainException;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

use function Amp\call;

final class MiddlewareBus implements Bus
{
    /**
     * @var Middleware[]
     */
    private array $middlewares = [];

    /**
     * @var null|callable(Message): Promise
     */
    private mixed $stack = null;

    public function __construct(Middleware ...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->append($middleware);
        }
    }

    /**
     * @return callable(Message): Promise
     */
    private function createStack(int $index = 0): callable
    {
        if (!isset($this->middlewares[$index])) {
            return static fn (Message $message) => new Success();
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

    public function handle(Message $message): Promise
    {
        return call(function () use ($message) {
            if (!isset($this->stack)) {
                $this->stack = $this->createStack();
            }

            return call($this->stack, $message);
        });
    }

    public function prepend(Middleware $middleware): void
    {
        if (isset($this->stack)) {
            throw new DomainException();
        }

        array_unshift($this->middlewares, $middleware);
    }
}
