<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Serialization\Serializer;
use Nayleen\Async\Bus\Message;

class Publisher
{
    public function __construct(private readonly Serializer $serializer) {}

    public function publish(Queue $queue, Message $message): void
    {
        $queue->enqueue($this->serializer->serialize($message));
    }
}
