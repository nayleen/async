<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Serialization\Serializer;
use Amp\Success;
use Generator;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
class PublisherTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function publishes_after_serialization(): Generator
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('serialize')->with($message)->willReturn($encoded);

        $queue = $this->createMock(Queue::class);
        $queue->expects(self::once())->method('enqueue')->with($encoded)->willReturn(new Success());

        $publisher = new Publisher($serializer);
        yield $publisher->publish($queue, $message);
    }
}
