<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Amp\CancelledException;
use DI\Container;
use Nayleen\Async\Component\Component;
use Nayleen\Async\Component\Components;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Kernel\Exception\NotRunningException;
use Nayleen\Async\Kernel\Exception\ReloadException;
use Nayleen\Async\Kernel\Exception\StopException;
use Nayleen\Async\Kernel\Exception\TerminateException;
use Nayleen\Async\Runtime;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop\Driver as Loop;
use Throwable;

final class Kernel
{
    private ?string $callbackId = null;

    private ?Container $container = null;

    private ?Throwable $failure = null;

    private ?Loop $loop = null;

    public readonly Components $components;

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(iterable $components = new Finder())
    {
        $this->components = new Components([Bootstrapper::class, ...$components]);
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     */
    private function handleFailure(): bool
    {
        if ($this->failure === null) {
            return false;
        }

        return match (true) {
            $this->failure instanceof CancelledException,
            $this->failure instanceof StopException,
            $this->failure instanceof TerminateException => false,
            $this->failure instanceof ReloadException => $this->handleReload(),
            default => throw $this->failure,
        };
    }

    /**
     * @return true
     */
    private function handleReload(): bool
    {
        assert(isset($this->container));

        $this->failure = null;
        $this->components->reload($this->container);
        gc_collect_cycles();

        return true;
    }

    public function boot(): Container
    {
        if (isset($this->container)) {
            return $this->container;
        }

        $container = $this->components->compile();
        $container->set(self::class, $this);

        $this->components->boot($container);

        return $this->container = $container;
    }

    public function fail(Throwable $failure): void
    {
        if (!isset($this->loop)) {
            throw new NotRunningException();
        }

        $this->failure = $failure;
        $this->stop();
    }

    /**
     * @api
     *
     * @template T of Runtime
     *
     * @param class-string<T> $runtimeClass
     * @param array<string, mixed> $parameters
     */
    public function make(string $runtimeClass, array $parameters = []): Runtime
    {
        assert($parameters === [] || !array_is_list($parameters));

        $runtime = $this->boot()->make($runtimeClass, $parameters);
        assert($runtime instanceof Runtime);

        return $runtime;
    }

    public function reload(): void
    {
        $this->fail(new ReloadException());
    }

    /**
     * @param callable(Kernel): void|null $callback
     */
    public function run(?callable $callback = null): void
    {
        $container = $this->boot();

        reload:
        $logger = $container->get(LoggerInterface::class);
        $loop = $container->get(Loop::class);

        $loop->queue(
            $logger->debug(...),
            sprintf('Kernel started using %s.', $loop::class),
        );

        if ($callback) {
            $this->callbackId = $loop->defer(function () use ($callback): void {
                try {
                    $callback($this);
                } catch (Throwable $ex) {
                    $this->fail($ex);
                }
            });
        }

        ($this->loop = $loop)->run();

        if (isset($this->callbackId)) {
            $loop->cancel($this->callbackId);
        }

        unset($this->loop, $loop);

        $reload = $this->handleFailure();

        if ($reload) {
            goto reload;
        }

        $this->shutdown();
    }

    public function shutdown(): void
    {
        if (isset($this->container)) {
            $this->components->shutdown($this->container);
        }

        unset($this->callbackId, $this->container, $this->failure, $this->loop);
    }

    public function stop(): void
    {
        if (!isset($this->loop)) {
            throw new NotRunningException();
        }

        if (isset($this->callbackId)) {
            $this->loop->cancel($this->callbackId);
        }

        $this->loop->queue($this->loop->stop(...));
    }

    public function terminate(): void
    {
        $this->fail(new TerminateException());
    }
}
