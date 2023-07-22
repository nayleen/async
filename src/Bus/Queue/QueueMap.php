<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Nayleen\Async\Bus\Message;
use OutOfBoundsException;

class QueueMap
{
    /**
     * @var array<string, Queue>
     */
    private array $map = [];

    /**
     * @param array<string, Queue> $map
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $name => $queue) {
            $this->add($name, $queue);
        }
    }

    private function add(string $name, Queue $queue): void
    {
        $this->map[$name] = $queue;
    }

    public function queue(Message $message): Queue
    {
        return $this->map[$message->name()] ?? throw new OutOfBoundsException();
    }
}
