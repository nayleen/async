<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Promise;
use Amp\Redis\Redis;
use Amp\Redis\RedisList;
use Amp\Success;

use function Amp\call;

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

    /**
     * @return Promise<null|string>
     */
    public function consume(): Promise
    {
        return $this->list()->popHead();
    }

    /**
     * @return Promise<null>
     */
    public function enqueue(string $message): Promise
    {
        return call(function () use ($message) {
            yield $this->list()->pushTail($message);

            return new Success();
        });
    }

    public function name(): string
    {
        return $this->name;
    }
}
