<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Nayleen\Async\Bus\Queue\Redis\Connection;

/**
 * @api
 */
class RedisQueue implements Queue
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $name,
    ) {
    }

    public function consume(): ?string
    {
        return $this->connection->popListHead($this->name);
    }

    public function enqueue(string $message): void
    {
        $this->connection->pushListTail($this->name, $message);
    }

    public function name(): string
    {
        return $this->name;
    }
}
