<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as TaskInterface;
use Closure;
use Override;

readonly class Worker extends Runtime implements TaskInterface
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
