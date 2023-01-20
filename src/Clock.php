<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\CancelledException;
use DateTimeImmutable;
use DateTimeZone;
use Revolt\EventLoop;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MonotonicClock;

use function Amp\delay;

final class Clock implements ClockInterface
{
    public function __construct(
        private readonly EventLoop\Driver $loop,
        private MonotonicClock $clock,
    ) {

    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }

    public function sleep(float|int $seconds): void
    {
        $suspension = $this->loop->getSuspension();
        $callbackId = $this->loop->unreference($this->loop->delay($seconds, static fn () => $suspension->resume()));

        try {
            $suspension->suspend();
        } finally {
            $this->loop->cancel($callbackId);
        }
    }

    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        $this->clock = $this->clock->withTimeZone($timezone);

        return $this;
    }
}
