<?php

declare(strict_types = 1);

namespace Nayleen\Async;

class Timers
{
    /**
     * @var Timer[]
     */
    private array $timers;

    public function __construct(Timer ...$timers)
    {
        $this->timers = $timers;
    }

    public function __destruct()
    {
        $this->stop();
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

    public function start(Kernel $kernel): void
    {
        foreach ($this->timers as $timer) {
            $timer->start($kernel);
        }
    }

    public function stop(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }

    public function suspendFor(float|int $duration): void
    {
        foreach ($this->timers as $timer) {
            $timer->suspendFor($duration);
        }
    }
}
