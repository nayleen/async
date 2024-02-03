<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Closure;
use Override;

abstract readonly class Application extends Runtime
{
    use ForbidCloning;
    use ForbidSerialization;

    public function __construct(Closure $closure, public Tasks $tasks = new Tasks())
    {
        parent::__construct($closure);
    }

    #[Override]
    public function execute(Kernel $kernel): mixed
    {
        $this->tasks->submit($kernel->scheduler);

        return parent::execute($kernel);
    }
}
