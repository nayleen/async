<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Serialization\Serializer;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
final class PublisherTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function publishes_after_serialization(): void
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('serialize')->with($message)->willReturn($encoded);

        $queue = $this->createMock(Queue::class);
        $queue->expects(self::once())->method('enqueue')->with($encoded);

        $publisher = new Publisher($serializer);
        $publisher->publish($queue, $message);
    }
}
