<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\CompositeCancellation;
use Amp\DeferredCancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Sync\Channel;
use DI;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Task\Scheduler;
use Revolt\EventLoop;
use Throwable;

class Kernel
{
    use ForbidCloning;
    use ForbidSerialization;

    private readonly Cancellation $cancellation;

    private DI\Container $container;

    private readonly DeferredCancellation $deferredCancellation;

    private readonly Scheduler $scheduler;

    /**
     * @internal Nayleen\Async
     */
    public readonly Components $components;

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(
        iterable $components = new Finder(),
        ?Channel $channel = null,
        Cancellation $cancellation = new NullCancellation(),
    ) {
        $this->deferredCancellation = new DeferredCancellation();
        $this->cancellation = new CompositeCancellation(
            $this->deferredCancellation->getCancellation(),
            $cancellation,
        );

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
    }

    public function cancellation(): Cancellation
    {
        return $this->cancellation;
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

        $this->container = $this->components->compile();

        assert($this->io()->debug('Booting Kernel', ['loop_driver' => $this->loop()::class]));
        $this->components->boot($this);

        return $this->container;
    }

    /**
     * @return non-empty-string
     */
    public function environment(): string
    {
        $env = $this->container()->get('async.env');
        assert(is_string($env) && $env !== '');

        return $env;
    }

    public function handle(Throwable $throwable): void
    {
        /**
         * @var callable(Throwable): void $exceptionHandler
         */
        $exceptionHandler = $this->container()->get('async.exception_handler');
        assert(is_callable($exceptionHandler));

        $exceptionHandler($throwable);
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
     * @template T of mixed
     * @param callable(Kernel): (Future<T>|T) $callback
     */
    public function run(callable $callback): mixed
    {
        reload:
        try {
            $return = $callback($this);

            if ($return instanceof Future) {
                $return = $return->await($this->cancellation);
            }
        } catch (CancelledException) {
        } catch (ReloadException) {
            assert($this->io()->debug('Reloading Kernel'));
            $this->components->reload($this);
            goto reload;
        } catch (StopException $stop) {
            if ($stop->signal !== null) {
                assert($this->io()->debug('Received signal ' . $stop->signal));
                $return ??= $stop->signal;
            }

            assert($this->io()->debug('Stopping Kernel'));
            $this->deferredCancellation->cancel($stop);
        }

        assert($this->io()->debug('Shutting down Kernel'));
        $this->components->shutdown($this);

        return $return ?? null;
    }

    public function scheduler(): Scheduler
    {
        return $this->scheduler;
    }
}
