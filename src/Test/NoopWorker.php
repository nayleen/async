<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Kernel;
use Nayleen\Async\Worker;

final readonly class NoopWorker extends Worker
{
    public function __construct(?Kernel $kernel = null)
    {
        parent::__construct(static function (): void {}, $kernel);
    }
}
