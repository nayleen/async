<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Event;

use Amp\Promise;
use Nayleen\Async\Bus\Handler\Validator;
use Nayleen\Async\Bus\Message;

final class Handlers
{
    use Validator;

    /**
     * @var array<string, array<array-key, callable(Message): Promise>>
     */
    private array $handlers = [];

    /**
     * @param array<string, list<callable(Message): Promise>> $handlers
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
     * @param callable(Message): Promise $handler
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
     * @return array<array-key, callable(Message): Promise>
     */
    public function filter(Message $message): array
    {
        return $this->handlers[$message->name()] ?? [];
    }
}
