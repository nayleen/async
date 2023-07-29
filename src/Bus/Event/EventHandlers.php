<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Closure;
use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;

class EventHandlers
{
    /**
     * @var array<string, array<int, Closure(Message): void>>
     */
    private array $handlers = [];

    /**
     * @param array<string, list<Closure(Message): void>> $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $name => $eventHandlers) {
            foreach ($eventHandlers as $handler) {
                $this->add($name, $handler);
            }
        }
    }

    /**
     * @param Closure(Message): void $handler
     */
    public function add(string $name, Closure $handler): void
    {
        assert(Validator::validate($handler));

        if (!isset($this->handlers[$name])) {
            $this->handlers[$name] = [];
        }

        $this->handlers[$name][] = $handler;
    }

    /**
     * @return array<array-key, Closure(Message): void>
     */
    public function filter(Message $message): array
    {
        return $this->handlers[$message->name()] ?? [];
    }
}
