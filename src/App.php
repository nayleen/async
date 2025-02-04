<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use DI;
use Nayleen\Async\App\Timers;
use Nayleen\Async\App\Workers;

readonly class App extends Runtime
{
    use ForbidCloning;
    use ForbidSerialization;

    private Kernel $kernel;

    public function __construct(
        ?Kernel $kernel = null,
        private Timers $timers = new Timers(),
        private Workers $workers = new Workers(),
    ) {
        $this->kernel = $kernel ?? new Kernel();

        parent::__construct($this->start(...), $this->kernel);
    }

    private function start(Kernel $kernel): int
    {
        $this->timers->start($kernel);
        $this->workers->start($kernel);

        $kernel->trap();

        return 0;
    }

    /**
     * @param class-string<Timer>|string|Timer $timer
     */
    public function addTimer(string|Timer $timer): static
    {
        if (is_string($timer)) {
            $timer = $this->container()->get($timer);
            assert($timer instanceof Timer);
        }

        $this->timers->add($timer);

        return $this;
    }

    /**
     * @param class-string<Worker>|string|Worker $worker
     * @param positive-int $count
     */
    public function addWorker(string|Worker $worker, int $count = 1): static
    {
        assert($count >= 1);

        if (is_string($worker)) {
            $worker = $this->container()->get($worker);
            assert($worker instanceof Worker);
        }

        $this->workers->add($worker, $count);

        return $this;
    }

    public function container(): DI\Container
    {
        return $this->kernel->container();
    }
}
