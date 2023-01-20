<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Nayleen\Async\Exception\SetupException;
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
        /**
         * @psalm-suppress RedundantPropertyInitializationCheck
         */
        if (isset($this->callbackId)) {
            $this->cancel();
            unset($this->callbackId, $this->loop);
        }
    }

    public function setup(EventLoop\Driver $loop): self
    {
        $copy = clone $this;
        $copy->callbackId = $loop->unreference($loop->defer($this->run(...)));
        $copy->loop = $loop;

        return $copy;
    }

    final protected function callbackId(): string
    {
        /**
         * @psalm-suppress RedundantPropertyInitializationCheck
         */
        assert(isset($this->callbackId), SetupException::notSetupCorrectly(self::class));

        return $this->callbackId;
    }

    abstract protected function execute(): void;

    abstract protected function interval(): float|int;

    final public function cancel(): void
    {
        $this->loop->cancel($this->callbackId());
    }

    final public function disable(): void
    {
        $this->loop->disable($this->callbackId());
    }

    final public function enable(): void
    {
        $this->loop->enable($this->callbackId());
    }

    final public function run(): void
    {
        $this->callbackId();

        while (true) {
            $this->suspendFor($this->interval());
            $this->execute();
        }
    }

    final public function suspendFor(float|int $duration): void
    {
        $this->disable();
        $this->loop->unreference($this->loop->delay((float) $duration, $this->enable(...)));
    }
}
