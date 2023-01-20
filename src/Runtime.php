<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Amp\Cancellation;
use Amp\DeferredCancellation;
use Nayleen\Async\Kernel\Exception\StopException;
use Nayleen\Async\Kernel\Exception\TerminateException;
use Revolt\EventLoop;

abstract class Runtime
{
    private readonly DeferredCancellation $cancellation;

    public function __construct(private readonly Kernel $kernel)
    {
        $this->cancellation = new DeferredCancellation();
    }

    protected function setup(EventLoop\Driver $loop): void
    {
        $loop->onSignal(SIGHUP, $this->kernel->reload(...));
        $loop->onSignal(SIGINT, fn () => $this->cancellation->cancel(new StopException()));
        $loop->onSignal(SIGTERM, fn () => $this->cancellation->cancel(new TerminateException()));
    }

    protected function cancellation(): Cancellation
    {
        return $this->cancellation->getCancellation();
    }

    abstract protected function execute(): void;

    final public function run(): void
    {
        $container = $this->kernel->boot();

        $this->setup($container->get(EventLoop\Driver::class));

        $this->kernel->run(function (Kernel $kernel): void {
            $this->execute();
            $kernel->stop();
        });
    }
}
