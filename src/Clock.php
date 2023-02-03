<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DateTimeImmutable;
use DateTimeZone;
use Revolt\EventLoop;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MonotonicClock;

/**
 * @api
 */
final class Clock implements ClockInterface
{
    private readonly ClockInterface $clock;

    private readonly DateTimeZone $timezone;

    public function __construct(
        private readonly EventLoop\Driver $loop,
        ?ClockInterface $clock = null,
        string|DateTimeZone|null $timezone = null,
    ) {
        $this->timezone = match (true) {
            $timezone === null => new DateTimeZone(date_default_timezone_get()),
            is_string($timezone) => new DateTimeZone($timezone),
            $timezone instanceof DateTimeZone => $timezone,
        };

        $this->clock = $clock ?? new MonotonicClock($this->timezone);
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }

    public function sleep(float|int $seconds): void
    {
        $suspension = $this->loop->getSuspension();
        $callbackId = $this->loop->unreference(
            $this->loop->delay($seconds, static fn () => $suspension->resume()),
        );

        try {
            $suspension->suspend();
        } finally {
            $this->loop->cancel($callbackId);
        }
    }

    public function timeZone(): DateTimeZone
    {
        return $this->timezone;
    }

    public function withTimeZone(DateTimeZone|string $timezone): static
    {
        return new self(
            $this->loop,
            $this->clock->withTimeZone($timezone),
            $timezone,
        );
    }
}
