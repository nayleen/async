<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Kernel;
use Nayleen\Async\Task;

/**
 * @template-extends Task<mixed, mixed, int>
 */
final readonly class TestTask extends Task
{
    public function __construct(?Kernel $kernel = null)
    {
        parent::__construct(static fn (): int => 69, $kernel);
    }
}
