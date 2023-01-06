<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use DI\Container;
use LogicException;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Runtime\Loop;
use Nayleen\Async\Runtime\Runtime;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

final class Kernel
{
    public readonly Components $components;

    private ?Container $container = null;

    private ?EventLoop\Driver $loop = null;

    private bool $reload = false;

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
     * @psalm-internal Nayleen\Async
     * @template T of Runtime
     *
     * @param class-string<T> $runtime
     * @return T
     */
    public function create(string $runtime, array $parameters = []): Runtime
    {
        $this->boot();

        $runtime = $this->container->make($runtime, $parameters);
        assert($runtime instanceof Runtime);

        return $runtime;
    }

    public function reload(): void
    {
        $this->reload = true;
        $this->stop();
    }

    /**
     * @param callable(Kernel): void|null $callback
     */
    public function run(?callable $callback = null): void
    {
        return $this->create(Loop::class)->defer($callback)->run();
        $container = $this->boot();

        reload:
        $loop = $container->get(EventLoop\Driver::class);
        $loop->queue(
            fn (LoggerInterface $logger) => $logger->debug(sprintf('Kernel started using %s.', $loop::class)),
            $container->get(LoggerInterface::class),
        );

        if ($callback) {
            $loop->defer(fn () => $callback($this));
        }

        ($this->loop = $loop)->run();
        unset($this->loop, $loop);

        $reload = $this->reload;
        $this->reload = false;

        if ($reload) {
            $this->components->reload($container);
            gc_collect_cycles();

            goto reload;
        }

        $this->shutdown();
    }

    public function running(): bool
    {
        return isset($this->container, $this->loop) && $this->loop->isRunning();
    }

    public function shutdown(): void
    {
        if ($this->booted()) {
            $this->components->shutdown($this->container);
        }

        unset($this->container, $this->loop);
    }

    public function stop(): void
    {
        if (!$this->running()) {
            throw new LogicException('Kernel is not running.');
        }

        $this->loop->queue(fn () => $this->loop->stop());
    }
}
