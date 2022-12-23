<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use LogicException;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Component\Finder;
use Nayleen\Async\Kernel\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

final class Kernel
{
    private ?Container $container = null;

    private ?EventLoop\Driver $loop = null;

    private bool $reload = false;

    public function __construct(
        private readonly Components $components,
        private readonly Container $serviceProvider,
    ) {

    }

    public function __destruct()
    {
        $this->shutdown();
    }

    private function shutdown(): void
    {
        foreach ($this->components->reverse() as $component) {
            $component->shutdown($this->container);
        }

        $this->container = null;
    }

    /**
     * @param iterable<class-string<Component>>|Finder $components
     */
    public static function create(
        iterable|Finder $components,
        ?ContainerInterface $delegateLookupContainer = null,
    ) {
        $container = new Container();

        if ($delegateLookupContainer) {
            $container->add($delegateLookupContainer);
        }

        $kernelComponents = new Components($container);
        foreach ($components as $component) {
            $kernelComponents->add($container->make($component));
        }

        return new self($kernelComponents, $container);
    }

    public function boot(): ContainerInterface
    {
        if ($this->booted()) {
            return $this->container;
        }

        $container = (clone $this->serviceProvider);
        $container->set(EventLoop\Driver::class, EventLoop::getDriver());
        $container->set(LoggerInterface::class, new NullLogger());
        $container->set(self::class, $this);

        foreach ($this->components as $component) {
            $container->add($component->register(clone $container));
        }

        foreach ($this->components as $component) {
            $component->boot($container);
        }

        return $this->container = $container;
    }

    public function booted(): bool
    {
        return isset($this->container);
    }

    public function components(): Components
    {
        return $this->components;
    }

    public function container(): ContainerInterface
    {
        if (!$this->booted()) {
            throw new LogicException();
        }

        return $this->container;
    }

    public function loop(): EventLoop\Driver
    {
        if (!$this->running()) {
            throw new LogicException();
        }

        return $this->loop;
    }

    /**
     * @param callable(Kernel): void|null $callback
     */
    public function run(?callable $callback = null): void
    {
        reload:
        $container = $this->boot();

        $loop = $container->get(EventLoop\Driver::class);
        $loop->queue(
            fn (LoggerInterface $logger) => $logger->debug(sprintf('Loop started using %s.', $loop::class)),
            $container->get(LoggerInterface::class),
        );

        if ($callback) {
            $loop->defer(fn () => $callback($this));
        }

        ($this->loop = $loop)->run();
        unset($loop, $this->loop);

        $reload = $this->reload;
        $this->reload = false;

        $this->shutdown();

        if ($reload) {
            goto reload;
        }
    }

    public function running(): bool
    {
        return isset($this->container, $this->loop) && $this->loop->isRunning();
    }

    public function stop(bool $reload = false): void
    {
        if (!$this->running()) {
            throw new \LogicException();
        }

        $this->reload = $reload;
        $this->loop->queue(fn () => $this->loop->stop());
    }
}
