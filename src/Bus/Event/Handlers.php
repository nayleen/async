<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;

/**
 * @api
 */
class Handlers
{
    use Validator;

    /**
     * @var array<string, array<array-key, callable(Message): void>>
     */
    private array $handlers = [];

    /**
     * @param array<string, list<callable(Message): void>> $handlers
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
     * @param callable(Message): void $handler
     */
    public function add(string $name, callable $handler): void
    {
        assert($this->validateHandler($handler));

        if (!isset($this->handlers[$name])) {
            $this->handlers[$name] = [];
        }

        $this->handlers[$name][] = $handler;
    }

    /**
     * @return array<array-key, callable(Message): void>
     */
    public function filter(Message $message): array
    {
        return $this->handlers[$message->name()] ?? [];
    }
}
