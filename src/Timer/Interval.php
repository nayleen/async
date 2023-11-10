<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Nayleen\Async\Timer;

abstract class Interval extends Timer
{
    public function __construct(private readonly float|int $interval) {}

    protected function interval(): float|int
    {
        return $this->interval;
    }
}
