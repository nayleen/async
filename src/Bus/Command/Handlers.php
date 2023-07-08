<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;

/**
 * @api
 */
class Handlers
{
    use Validator;

    /**
     * @var array<string, callable(Message): void>
     */
    private array $handlers = [];

    /**
     * @param array<string, callable(Message): void> $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $name => $handler) {
            $this->add($name, $handler);
        }
    }

    /**
     * @param callable(Message): void $handler
     */
    public function add(string $name, callable $handler): void
    {
        assert($this->validateHandler($handler));
        $this->handlers[$name] = $handler;
    }

    /**
     * @return callable(Message): void
     */
    public function find(Message $message): callable
    {
        return $this->handlers[$message->name()] ?? throw new OutOfBoundsException();
    }
}
