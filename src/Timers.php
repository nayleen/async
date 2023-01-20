<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Nayleen\Async\Clock;
use Nayleen\Async\Exception\SetupException;
use Revolt\EventLoop;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MonotonicClock;

/**
 * @api
 */
final class Timers
{
    private ClockInterface $clock;

    private bool $setup = false;

    /**
     * @var Timer[]
     */
    private array $timers;

    public function __construct(Timer ...$timers)
    {
        $this->clock = new MonotonicClock();
        $this->timers = $timers;
    }

    public function __destruct()
    {
        if ($this->setup) {
            $this->cancel();
            unset($this->clock, $this->timers);
        }
    }

    public function setup(EventLoop\Driver $loop): self
    {
        /*
         * @psalm-suppress RedundantPropertyInitializationCheck
         */
        assert($this->setup === false, SetupException::alreadySetup(self::class));

        $clock = new Clock($loop, $this->clock);

        $copy = new self();
        foreach ($this->timers as $i => $timer) {
            $copy->timers[$i] = match ($timer::class) {
                Cron::class => $timer->setup($loop, $clock),
                default => $timer->setup($loop),
            };
        }

        $copy->setup = true;

        return $copy;
    }

    public function cancel(): void
    {
        foreach ($this->timers as $timer) {
            $timer->cancel();
        }
    }

    public function disable(): void
    {
        foreach ($this->timers as $timer) {
            $timer->disable();
        }
    }

    public function enable(): void
    {
        foreach ($this->timers as $timer) {
            $timer->enable();
        }
    }

    public function suspendFor(float|int $duration): void
    {
        foreach ($this->timers as $timer) {
            $timer->suspendFor($duration);
        }
    }

    /**
     * @internal
     */
    public function withClock(ClockInterface $clock): self
    {
        assert($this->setup === false, SetupException::alreadySetup(self::class));

        $copy = clone $this;
        $copy->clock = $clock;

        return $copy;
    }
}
