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

class Consumer
{
    private const DEFAULT_CONSUME_DELAY = 0.050; // ms

    public function __construct(
        private readonly Bus $bus,
        private readonly Serializer $serializer,
        private readonly float $consumeDelay = self::DEFAULT_CONSUME_DELAY,
    ) {}

    public function consume(
        Queue $queue,
        Cancellation $cancellation = new NullCancellation(),
    ): void {
        try {
            while (!$cancellation->isRequested()) {
                $cancellation->throwIfRequested();

                $encoded = $queue->consume();
                if ($encoded === null) {
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
