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
use DI\Container;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Task\Scheduler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Revolt\EventLoop;

class Kernel
{
    use ForbidCloning;
    use ForbidSerialization;

    private Container $container;

    private readonly DeferredCancellation $deferredCancellation;

    public readonly Cancellation $cancellation;

    public readonly Components $components;

    public readonly Scheduler $scheduler;

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
        $this->scheduler = new Scheduler($this);

        $this->components = new Components(
            [
                Bootstrapper::class,
                DependencyProvider::create([
                    self::class => $this,
                    Cancellation::class => $this->cancellation,
                    Channel::class => $channel,
                    Scheduler::class => $this->scheduler,
                ]),
                ...$components,
            ],
        );
    }

    public function clock(): Clock
    {
        return $this->container()->get(Clock::class);
    }

    public function container(): Container
    {
        if (isset($this->container)) {
            return $this->container;
        }

        $this->container = $this->components->compile();
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

    public function loop(): EventLoop\Driver
    {
        return $this->container()->get(EventLoop\Driver::class);
    }

    public function reload(): never
    {
        throw new ReloadException();
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
            $this->components->reload($this);
            goto reload;
        } catch (StopException $stop) {
            if ($stop->signal !== null) {
                assert($this->writeDebug('Received signal ' . $stop->signal));
                $return ??= $stop->signal;
            }

            assert($this->writeDebug('Stopping Kernel'));
            $this->deferredCancellation->cancel($stop);
        }

        $this->components->shutdown($this);

        return $return ?? null;
    }

    public function stop(?int $signal = null): never
    {
        throw new StopException($signal);
    }

    /**
     * @param mixed[] $context
     */
    public function write(string $level, string $message, array $context = []): bool
    {
        /**
         * @var LoggerInterface $stdOut
         */
        $stdOut = $this->container()->get('async.logger');
        $stdOut->log($level, $message, $context);

        return true;
    }

    /**
     * @param mixed[] $context
     */
    public function writeDebug(string $message, array $context = []): bool
    {
        /**
         * @var LoggerInterface $stdErr
         */
        $stdErr = $this->container()->get('async.logger.debug');
        $stdErr->log(LogLevel::DEBUG, $message, $context);

        return true;
    }
}
