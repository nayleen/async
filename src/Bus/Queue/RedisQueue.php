<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Redis\Redis;
use Amp\Redis\RedisList;

final class RedisQueue implements Queue
{
    private ?RedisList $list = null;

    public function __construct(
        private readonly Redis $redis,
        private readonly string $name,
    ) {
    }

    private function list(): RedisList
    {
        if (!isset($this->list)) {
            $this->list = $this->redis->getList($this->name);
        }

        return $this->list;
    }

    public function consume(): ?string
    {
        return $this->list()->popHead();
    }

    public function enqueue(string $message): void
    {
        $this->list()->pushTail($message);
    }

    public function name(): string
    {
        return $this->name;
    }
}
