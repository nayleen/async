<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\Cluster\Cluster;
use Amp\CompositeCancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\NullCancellation;
use Amp\Sync\Channel;
use DI;
use Nayleen\Async\Component\Bootstrapper;
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
        iterable $components = new Finder(), // @phpstan-ignore-line - Traversable IS iterable
        ?Channel $channel = null,
        ?Cancellation $cancellation = null,
    ) {
        $this->cancellation = $cancellation ?? new NullCancellation();
        $this->scheduler = new Scheduler($this);

        $this->components = new Components(
            [
                Bootstrapper::class,
                DependencyProvider::create([
                    self::class => $this,
                    Cancellation::class => $this->cancellation,
                    Channel::class => static fn (): ?Channel => $channel,
                    Scheduler::class => $this->scheduler,
                ]),
                ...$components,
            ],
        );
    }

    public function channel(): Channel
    {
        return $this->container()->get(Channel::class);
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

    public function trap(Cancellation $cancellation = new NullCancellation()): void
    {
        assert($this->io()->info('Awaiting shutdown via signals', ['trapped' => Cluster::getSignalList()]));

        try {
            Cluster::awaitTermination(new CompositeCancellation($this->cancellation, $cancellation));
        } finally {
            assert($this->io()->notice('Received shutdown request'));
        }
    }
}
