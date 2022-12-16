<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\Promise;
use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;

final class Handlers
{
    use Validator;

    /**
     * @var array<string, callable(Message): Promise>
     */
    private array $handlers = [];

    /**
     * @param array<string, callable(Message): Promise> $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $name => $handler) {
            $this->add($name, $handler);
        }
    }

    /**
     * @param callable(Message): Promise $handler
     */
    public function add(string $name, callable $handler): void
    {
        assert($this->validateHandler($handler));

        $this->handlers[$name] = $handler;
    }

    /**
     * @return callable(Message): Promise
     */
    public function find(Message $message): callable
    {
        $name = $message->name();

        return $this->handlers[$name] ?? throw new OutOfBoundsException();
    }
}
