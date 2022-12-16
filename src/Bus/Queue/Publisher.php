<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Promise;
use Amp\Serialization\Serializer;
use Nayleen\Async\Bus\Message;

final class Publisher
{
    public function __construct(private readonly Serializer $serializer)
    {
    }

    public function publish(Queue $queue, Message $message): Promise
    {
        return $queue->enqueue($this->serializer->serialize($message));
    }
}
