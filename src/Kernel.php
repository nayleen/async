<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\CompositeCancellation;
use Amp\DeferredCancellation;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Sync\Channel;
use DI;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Revolt\EventLoop;

/**
 * @api
 */
final class Kernel
{
    private readonly Components $components;

    private readonly DI\Container $container;

    private readonly DeferredCancellation $deferredCancellation;

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(
        iterable $components = new Finder(),
        ?Channel $channel = null,
        Cancellation $cancellation = new NullCancellation(),
    ) {
        $this->components = new Components(
            [
                Bootstrapper::class,
                DependencyProvider::create([Channel::class => $channel]),
                ...$components,
            ]
        );

        $this->deferredCancellation = new DeferredCancellation();
        $this->cancellation = new CompositeCancellation($this->deferredCancellation->getCancellation(), $cancellation);
    }

    public function __destruct()
    {
        if (isset($this->container)) {
            $this->components->shutdown($this);
        }
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

        $this->container = $this->components->compile(new DI\ContainerBuilder());
        unset($this->channel);

        $this->container->set(self::class, $this);
        $this->components->boot($this);

        return $this->container;
    }

    /**
     * @return non-empty-string
     */
    public function environment(): string
    {
        return $this->container()->get('async.env');
    }

    /**
     * @param class-string<Runtime> $runtime
     * @param array<string, string> $parameters
     */
    public function execute(string $runtime, array $parameters = []): int
    {
        $container = $this->container();

        $runtime = $container->make($runtime, $parameters);
        assert($runtime instanceof Runtime);

        return $container->call($runtime, $parameters);
    }

    public function loop(): EventLoop\Driver
    {
        return $this->container()->get(EventLoop\Driver::class);
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $class
     * @param array<class-string|string, string> $parameters
     * @return T
     */
    public function make(string $class, array $parameters = []): mixed
    {
        return $this->container()->make($class, $parameters);
    }

    /**
     * @param callable(Kernel): (Future|null) $callback
     */
    public function run(callable $callback): mixed
    {
        reload:
        $loop = $this->loop();

        try {
            $future = $callback($this);
            $return = $future?->await($this->cancellation());

            $loop->run();
        } catch (CancelledException) {
        } catch (ReloadException) {
            $this->components->reload($this);
            gc_collect_cycles();

            goto reload;
        } catch (StopException) {
            assert($this->writeDebug('Stopping Kernel'));
        }

        return $return ?? null;
    }

    public function write(string $level, string $message, array $context = []): bool
    {
        /**
         * @var LoggerInterface $stdOut
         */
        $stdOut = $this->container()->get('async.logger.stdout');
        $stdOut->log($level, $message, $context);

        return true;
    }

    public function writeDebug(string $message, array $context = []): bool
    {
        /**
         * @var LoggerInterface $stdErr
         */
        $stdErr = $this->container()->get('async.logger.stderr');
        $stdErr->log(LogLevel::DEBUG, $message, $context);

        return true;
    }
}
