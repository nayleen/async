<?php

declare(strict_types = 1);

namespace Nayleen\Async;

/**
 * @internal
 */
abstract class Timer
{
    private string $callbackId;

    protected Kernel $kernel;

    public function __destruct()
    {
        $this->stop();
    }

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
        $this->kernel = $kernel;
        $this->callbackId = $this->kernel->loop()->unreference(
            $this->kernel->loop()->defer($this->run(...)),
        );
    }

    public function stop(): void
    {
        $this->kernel->loop()->cancel($this->callbackId);
    }

    public function suspend(float|int $duration): void
    {
        $this->disable();
        $this->kernel->loop()->unreference(
            $this->kernel->loop()->delay((float) $duration, $this->enable(...)),
        );
    }
}
