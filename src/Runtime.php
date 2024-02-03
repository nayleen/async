<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\Sync\Channel;
use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * A runtime is the basic building block for code intended for use in
 * a separate process/thread, be it a one-off {@see Task}, a long-running {@see Worker},
 * a {@see Cluster} of workers, or your main {@see Application}.
 *
 * @psalm-internal Nayleen\Async
 *
 * @template TReceive
 * @template TSend
 * @template-covariant TResult
 */
abstract readonly class Runtime
{
    protected SerializableClosure $code;

    /**
     * @param Closure(Kernel): TResult $closure
     */
    public function __construct(Closure $closure)
    {
        assert((static function () use ($closure): bool {
            $reflection = new ReflectionFunction($closure);
            $parameters = $reflection->getParameters();
            $parameterCount = count($parameters);

            // callable doesn't use Kernel
            if ($parameterCount === 0) {
                return true;
            }

            // ensure the first parameter is Kernel
            if (
                !(($type = $parameters[0]->getType()) instanceof ReflectionNamedType)
                || $type->getName() !== Kernel::class
            ) {
                return false;
            }

            return true;
        })());

        $this->code = new SerializableClosure($closure);
    }

    /**
     * This is marked as internal for good reason. It's supposed to only be called
     * by either the {main} Fiber through {@see run()} as the entry point of an {@see Application},
     * or as the primary executable when running in a separate process/thread.
     *
     * For testing purposes it's way more convenient to leave it open, unfortunately.
     *
     * @psalm-internal Nayleen\Async
     * @return TResult
     */
    public function execute(Kernel $kernel): mixed
    {
        return ($this->code)($kernel);
    }

    /**
     * @param Channel<TReceive, TSend>|null $channel
     * @return TResult|null
     */
    final public function run(
        ?Channel $channel = null,
        ?Cancellation $cancellation = null,
    ): mixed {
        return (new Kernel(channel: $channel, cancellation: $cancellation))->run($this->execute(...));
    }
}
