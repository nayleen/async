<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Nayleen\Async\Bus\Queue\Redis\Connection;

class RedisQueue implements Queue
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $name,
    ) {
        assert($this->name !== '');
    }

    public function consume(): ?string
    {
        return $this->connection->popListHead($this->name);
    }

    public function enqueue(string $message): void
    {
        $this->connection->pushListTail($this->name, $message);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
}
