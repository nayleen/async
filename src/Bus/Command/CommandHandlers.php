<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Closure;
use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;
use OutOfBoundsException;

class CommandHandlers
{
    /**
     * @var array<string, Closure(Message): void>
     */
    private array $handlers = [];

    /**
     * @param array<string, Closure(Message): void> $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $name => $handler) {
            $this->add($name, $handler);
        }
    }

    /**
     * @param Closure(Message): void $handler
     */
    public function add(string $name, Closure $handler): void
    {
        assert(Validator::validate($handler));
        $this->handlers[$name] = $handler;
    }

    /**
     * @return Closure(Message): void
     */
    public function find(Message $message): Closure
    {
        return $this->handlers[$message->name()] ?? throw new OutOfBoundsException();
    }
}
