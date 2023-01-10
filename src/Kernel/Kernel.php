<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use DI\Container;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Exception\NotRunningException;
use Nayleen\Async\Kernel\Exception\ReloadException;
use Nayleen\Async\Kernel\Exception\StopException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop\Driver as Loop;
use Throwable;

final class Kernel
{
    public readonly Components $components;

    private ?Container $container = null;

    private ?Throwable $failure = null;

    private ?Loop $loop = null;

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(iterable $components)
    {
        $this->components = new Components([Bootstrapper::class, ...$components]);
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    private function handleFailure(): bool
    {
        return match (true) {
            is_null($this->failure),
            $this->failure instanceof StopException => false,
            $this->failure instanceof ReloadException => $this->handleReload(),
            default => throw $this->failure,
        };
    }

    /**
     * @return true
     */
    private function handleReload(): bool
    {
        $this->failure = null;

        $this->components->reload($this->container);
        gc_collect_cycles();

        return true;
    }

    public function boot(): ContainerInterface
    {
        if ($this->booted()) {
            return $this->container;
        }

        $container = $this->components->compile();
        $container->set(self::class, $this);

        $this->components->boot($container);

        return $this->container = $container;
    }

    public function booted(): bool
    {
        return isset($this->container);
    }

    /**
     * @api
     * @template T of Runtime
     *
     * @param class-string<T> $runtime
     * @return T
     */
    public function create(string $runtime, array $parameters = []): Runtime
    {
        $this->boot();

        return $this->container->make($runtime, $parameters);
    }

    public function fail(Throwable $failure): void
    {
        if (!$this->running()) {
            throw new NotRunningException();
        }

        $this->failure = $failure;
        $this->stop();
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
            $loop->defer(function () use ($callback) {
                try {
                    $callback($this);
                } catch (Throwable $ex) {
                    $this->fail($ex);
                }
            });
        }

        ($this->loop = $loop)->run();
        unset($this->loop, $loop);

        $reload = $this->handleFailure();

        if ($reload) {
            goto reload;
        }

        $this->shutdown();
    }

    public function running(): bool
    {
        return !isset($this->failure)
            && isset($this->container, $this->loop)
            && $this->loop->isRunning();
    }

    public function shutdown(): void
    {
        if ($this->booted()) {
            $this->components->shutdown($this->container);
        }

        unset($this->container, $this->failure, $this->loop);
    }

    public function stop(): void
    {
        if (!$this->running()) {
            throw new NotRunningException();
        }

        $this->loop->queue($this->loop->stop(...));
    }
}
