<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Closure;
use Override;
use Throwable;

readonly class Worker extends Runtime
{
    /**
     * @param Closure(Kernel): void $closure
     */
    public function __construct(Closure $closure, ?Kernel $kernel = null)
    {
        parent::__construct($closure, $kernel);
    }

    /**
     * @return int<0, 255>
     */
    #[Override]
    protected function execute(Kernel $kernel): int
    {
        try {
            parent::execute($kernel);

            $kernel->trap();
        } catch (Throwable) {
            return 1;
        }

        return 0;
    }
}
