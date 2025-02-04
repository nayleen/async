<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Closure;

readonly class Worker extends Runtime
{
    /**
     * @param Closure(Kernel): void $closure
     */
    public function __construct(Closure $closure, ?Kernel $kernel = null)
    {
        parent::__construct($closure, $kernel);
    }

    protected function execute(Kernel $kernel): int
    {
        parent::execute($kernel);

        return 0;
    }
}
