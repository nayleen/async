<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\NullCancellationToken;
use Amp\Promise;
use Amp\Serialization\Serializer;
use Amp\Success;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

use function Amp\call;
use function Amp\delay;

final class Consumer
{
    private const DEFAULT_CONSUME_DELAY = 50; // ms

    public function __construct(
        private readonly Bus $bus,
        private readonly Serializer $serializer,
        private readonly int $consumeDelay = self::DEFAULT_CONSUME_DELAY,
    ) {
    }

    public function consume(Queue $queue, ?CancellationToken $cancellationToken = null): Promise
    {
        $cancellationToken ??= new NullCancellationToken();

        return call(function () use ($queue, $cancellationToken) {
            try {
                while (true) {
                    $cancellationToken->throwIfRequested();

                    $encoded = yield $queue->consume();
                    if (!$encoded) {
                        yield delay($this->consumeDelay);
                        continue;
                    }

                    $message = $this->serializer->unserialize($encoded);
                    assert($message instanceof Message);

                    yield $this->bus->handle($message);
                }
            } catch (CancelledException) {
            }

            return new Success();
        });
    }
}
