<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\Loop\Driver;
use Amp\Promise;
use LogicException;

abstract class Timer
{
    private Driver $loop;

    private string $watcherId;

    public function __construct(private readonly int $interval)
    {
    }

    public function setup(Driver $loop): void
    {
        $this->loop = $loop;

        $this->setupCancellation(
            $this->signals(),
            $this->watcherId = $loop->repeat($this->interval, $this),
        );

        $loop->unreference($this->watcherId);
    }

    private function loop(): Driver
    {
        return $this->loop;
    }

    public function suspend(int $duration): void
    {
        if (!$this->watcherId) {
            throw new LogicException();
        }

        $this->loop->disable($this->watcherId);
        $this->loop->delay($duration, fn () => $this->loop->enable($this->watcherId));
    }

    abstract public function __invoke(string $watcherId): Promise;
}
