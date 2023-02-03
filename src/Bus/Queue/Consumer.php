<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\NullCancellation;
use Amp\Serialization\Serializer;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

use function Amp\delay;

/**
 * @api
 */
final class Consumer
{
    private const DEFAULT_CONSUME_DELAY = 0.05; // ms

    public function __construct(
        private readonly Bus $bus,
        private readonly Serializer $serializer,
        private readonly float $consumeDelay = self::DEFAULT_CONSUME_DELAY,
    ) {
    }

    public function consume(Queue $queue, ?Cancellation $cancellation = null): void
    {
        $cancellation ??= new NullCancellation();

        try {
            while (true) {
                $cancellation->throwIfRequested();

                $encoded = $queue->consume();
                if (!$encoded) {
                    delay($this->consumeDelay, cancellation: $cancellation);
                    continue;
                }

                $message = $this->serializer->unserialize($encoded);
                assert($message instanceof Message);

                $this->bus->handle($message);
            }
        } catch (CancelledException) {
        }
    }
}
