<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

class InMemoryQueue implements Queue
{
    /**
     * @var string[]
     */
    private array $queue = [];

    public function __construct(private readonly string $name) {}

    public function consume(): ?string
    {
        if ($this->queue === []) {
            return null;
        }

        return array_shift($this->queue);
    }

    public function enqueue(string $message): void
    {
        $this->queue[] = $message;
    }

    public function name(): string
    {
        return $this->name;
    }
}
