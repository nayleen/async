<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Redis;

use Amp\Redis\RedisClient;

class Connection
{
    public function __construct(private readonly RedisClient $redis)
    {
    }

    public function popListHead(string $list): ?string
    {
        return $this->redis->getList($list)->popHead();
    }

    public function pushListTail(string $list, string ...$values): void
    {
        assert(count($values) > 0);
        $this->redis->getList($list)->pushTail(...$values);
    }
}
