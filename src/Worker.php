<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Closure;
use Override;

readonly class Worker extends Runtime
{
    public function __construct(Closure $closure)
    {
        parent::__construct($closure);
    }

    #[Override]
    protected function execute(Kernel $kernel): null
    {
        parent::execute($kernel);

        $kernel->trap();

        return null;
    }
}
