<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * @psalm-internal Nayleen\Async
 *
 * @template-covariant TResult
 */
class AnonymousTask extends Task
{
    private readonly SerializableClosure $code;

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
     * @param non-empty-string $script path to a script returning a closure compatible with {@see AnonymousTask::__construct}
     * @return AnonymousTask<mixed>
     */
    public static function fromScript(string $script): self
    {
        assert((static function () use ($script): bool {
            if (!file_exists($script)) {
                return false;
            }

            $return = require $script;
            if (!($return instanceof Closure)) {
                return false;
            }

            return true;
        })());

        return new self((require $script)(...));
    }

    /**
     * @return TResult
     */
    protected function execute(Kernel $kernel): mixed
    {
        return ($this->code)($kernel);
    }
}
