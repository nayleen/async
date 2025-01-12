<?php

declare(strict_types = 1);

namespace Nayleen\Async;

abstract readonly class Timer
{
    private string $callbackId; // @phpstan-ignore-line

    protected Kernel $kernel; // @phpstan-ignore-line

    abstract protected function execute(): void;

    abstract protected function interval(): float|int;

    public function disable(): void
    {
        $this->kernel->loop()->disable($this->callbackId);
    }

    public function enable(): void
    {
        $this->kernel->loop()->enable($this->callbackId);
    }

    public function run(): void
    {
        $this->execute();
        $this->suspend($this->interval());
    }

    public function start(Kernel $kernel): void
    {
        $this->kernel = $kernel; // @phpstan-ignore-line
        $this->callbackId = $this->kernel->loop()->unreference( // @phpstan-ignore-line
            $this->kernel->loop()->defer($this->run(...)),
        );
        $this->kernel->cancellation->subscribe($this->stop(...));
    }

    public function stop(): void
    {
        $this->kernel->loop()->cancel($this->callbackId);
    }

    /**
     * @param float|positive-int $duration
     */
    public function suspend(float|int $duration): void
    {
        assert($duration > 0);

        $this->disable();
        $this->kernel->loop()->unreference(
            $this->kernel->loop()->delay((float) $duration, $this->enable(...)),
        );
    }
}
