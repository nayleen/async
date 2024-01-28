<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\NullCancellation;
use Amp\Sync\Channel;
use DI;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Task\Scheduler;
use Revolt\EventLoop;

use function Amp\async;

readonly class Kernel
{
    use ForbidCloning;
    use ForbidSerialization;

    private DI\Container $container; // @phpstan-ignore-line

    private Signals $signals;

    public Cancellation $cancellation;

    /**
     * @psalm-internal Nayleen\Async
     */
    public Components $components;

    public Scheduler $scheduler;

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(
        iterable $components = new Finder(),
        ?Channel $channel = null,
        ?Cancellation $cancellation = null,
    ) {
        $this->cancellation = $cancellation ?? new NullCancellation();
        $this->components = new Components(
            [
                Bootstrapper::class,
                DependencyProvider::create([
                    self::class => $this,
                    Cancellation::class => $this->cancellation,
                    Channel::class => $channel,
                ]),
                ...$components,
            ],
        );
        $this->scheduler = new Scheduler($this);
        $this->signals = new Signals($this);
    }

    public function clock(): Clock
    {
        return $this->container()->get(Clock::class);
    }

    public function container(): DI\Container
    {
        if (isset($this->container)) {
            return $this->container;
        }

        $this->container = $this->components->compile(); // @phpstan-ignore-line
        $this->components->boot($this);

        return $this->container;
    }

    public function io(): IO
    {
        return $this->container()->get(IO::class);
    }

    public function loop(): EventLoop\Driver
    {
        return $this->container()->get(EventLoop\Driver::class);
    }

    /**
     * @template TResult of mixed
     * @param callable(Kernel): TResult $callback
     * @return TResult|null
     */
    public function run(callable $callback): mixed
    {
        try {
            return async($callback(...), $this)->await($this->cancellation);
        } catch (CancelledException) {
            return null;
        } finally {
            $this->components->shutdown($this);
        }
    }

    public function trap(int ...$signals): void
    {
        assert(count($signals) > 0);
        $this->signals->trap(...array_unique(array_values($signals)));
    }
}
