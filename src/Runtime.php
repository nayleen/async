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
 * A runtime is the basic building block for code intended for use in a process.
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
    public function __construct(Closure $closure, private ?Kernel $kernel = null)
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
     * @return TResult
     */
    protected function execute(Kernel $kernel): mixed
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
        $kernel = $this->kernel ?? new Kernel(channel: $channel, cancellation: $cancellation);

        return $kernel->run($this->execute(...));
    }
}
