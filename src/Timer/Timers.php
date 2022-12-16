<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\Loop\Driver;

final class Timers
{
    /**
     * @var Timer[]
     */
    private array $timers;

    public function __construct(Timer ...$timers)
    {
        $this->timers = $timers;
    }

    public function setup(Driver $loop): void
    {
        foreach ($this->timers as $timer) {
            $timer->setup($loop);
        }
    }
}
