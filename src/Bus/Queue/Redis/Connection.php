<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Redis;

use Amp\Redis\RedisClient;

class Connection
{
    public function __construct(private readonly RedisClient $redis) {}

    /**
     * @param non-empty-string $list
     */
    public function popListHead(string $list): ?string
    {
        assert($list !== '');

        return $this->redis->getList($list)->popHead();
    }

    /**
     * @param non-empty-string $list
     */
    public function pushListTail(string $list, string ...$values): void
    {
        assert($list !== '' && count($values) > 0);
        $this->redis->getList($list)->pushTail(...$values);
    }
}
