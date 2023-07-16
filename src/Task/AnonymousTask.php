<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Future;
use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;

/**
 * @internal
 *
 * @template-covariant TResult
 */
final class AnonymousTask extends Task
{
    private SerializableClosure $code;

    /**
     * @param Closure(Kernel): (Future<TResult>|TResult) $closure
     */
    public function __construct(Closure $closure)
    {
        $this->code = new SerializableClosure($closure);
    }

    /**
     * @return TResult
     */
    protected function execute(Kernel $kernel): mixed
    {
        return ($this->code)($kernel);
    }
}
