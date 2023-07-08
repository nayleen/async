<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Revolt\EventLoop;

/**
 * @internal
 */
abstract class Timer
{
    private string $callbackId;

    private EventLoop\Driver $loop;

    public function __destruct()
    {
        $this->stop();
    }

    abstract protected function execute(): void;

    abstract protected function interval(): float|int;

    public function disable(): void
    {
        $this->loop->disable($this->callbackId);
    }

    public function enable(): void
    {
        $this->loop->enable($this->callbackId);
    }

    public function run(): void
    {
        $this->execute();
        $this->suspendFor($this->interval());
    }

    public function start(Kernel $kernel): void
    {
        $this->loop = $kernel->loop();
        $this->callbackId = $this->loop->unreference($this->loop->defer($this->run(...)));
    }

    public function stop(): void
    {
        $this->loop->cancel($this->callbackId);
    }

    public function suspendFor(float|int $duration): void
    {
        $this->disable();
        $this->loop->unreference(
            $this->loop->delay((float) $duration, $this->enable(...)),
        );
    }
}
