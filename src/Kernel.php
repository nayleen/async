<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\CompositeCancellation;
use Amp\DeferredCancellation;
use Closure;
use DI\Container;
use Monolog\Logger;
use Revolt\EventLoop\Driver as EventLoop;

readonly class Kernel
{
    private DeferredCancellation $cancellation;

    /**
     * @param non-empty-string $env
     */
    public function __construct(
        public EventLoop $loop,
        public Logger $logger,
        public string $env,
        public bool $debug,
    ) {
        $this->cancellation = new DeferredCancellation();
    }

    public function cancellation(?Cancellation $cancellation = null): Cancellation
    {
        if (!isset($cancellation)) {
            return $this->cancellation->getCancellation();
        }

        return new CompositeCancellation(
            $this->cancellation->getCancellation(),
            $cancellation,
        );
    }

    /**
     * @param Closure(mixed ...$args): void $closure
     */
    public function run(Container $container, Closure $closure): void
    {
        try {
            $this->logger->debug('Kernel starting');

            // queue the "main" task and run the event loop
            $this->loop->queue(function () use ($container, $closure): void {
                $container->call($closure);
            });

            $this->loop->run();
        } finally {
            $this->loop->setErrorHandler(null);
            $this->logger->debug('Kernel stopped');
        }
    }

    public function stop(): void
    {
        $this->cancellation->cancel();
        $this->loop->queue(fn () => $this->loop->stop());
    }

    /**
     * @param int[] $stopSignals
     */
    public function trap(
        array $stopSignals = [SIGINT, SIGQUIT, SIGTERM],
        ?Cancellation $cancellation = null,
    ): void {
        $resumptions = [];

        $suspension = $this->loop->getSuspension();
        $resumeCallback = static fn (string $watcher, int $signal) => $suspension->resume($signal);

        foreach ($stopSignals as $signal) {
            $resumptions[] = $this->loop->onSignal($signal, $resumeCallback);
        }

        $cancellation = $this->cancellation($cancellation);
        $cancellationCallback = static fn (CancelledException $exception) => $suspension->throw($exception);
        $subscriber = $cancellation->subscribe($cancellationCallback);

        $this->logger->notice('Awaiting shutdown via signals', [
            'signals' => $stopSignals,
        ]);

        try {
            $signal = $suspension->suspend();
            assert(is_int($signal));
        } finally {
            foreach ($resumptions as $callbackId) {
                $this->loop->cancel($callbackId);
            }

            $cancellation->unsubscribe($subscriber);
            unset($cancellation);
        }

        $this->logger->notice('Received shutdown signal', [
            'signal' => $signal,
        ]);

        $this->stop();
    }
}
