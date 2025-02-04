<?php

declare(strict_types = 1);

namespace Nayleen\Async\App;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Nayleen\Async\Kernel;
use Nayleen\Async\Timer;
use SplObjectStorage;

/**
 * @psalm-internal Nayleen\Async
 */
class Timers
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var SplObjectStorage<Timer>
     */
    public readonly SplObjectStorage $timers;

    public function __construct()
    {
        $this->timers = new SplObjectStorage();
    }

    public function add(Timer $timer): void
    {
        $this->timers->attach($timer);
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

    /**
     * @param float|positive-int $duration
     */
    public function suspend(float|int $duration): void
    {
        assert($duration > 0);

        foreach ($this->timers as $timer) {
            $timer->suspend($duration);
        }
    }
}
