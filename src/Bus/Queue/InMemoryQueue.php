<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Promise;
use Amp\Success;

final class InMemoryQueue implements Queue
{
    /**
     * @var string[]
     */
    private array $queue = [];

    public function __construct(private readonly string $name)
    {
    }

    /**
     * @return Promise<null|string>
     */
    public function consume(): Promise
    {
        if ($this->queue === []) {
            return new Success();
        }

        return new Success(array_shift($this->queue));
    }

    /**
     * @return Promise<null>
     */
    public function enqueue(string $message): Promise
    {
        $this->queue[] = $message;

        return new Success();
    }

    public function name(): string
    {
        return $this->name;
    }
}
