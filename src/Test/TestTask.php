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
    protected ?Kernel $kernel;

    public function __construct(?Kernel $kernel = null)
    {
        $this->kernel = $kernel;
        parent::__construct(static fn (): int => 69);
    }
}
