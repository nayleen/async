<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Task;

/**
 * @template-extends Task<mixed, mixed, int>
 */
final readonly class TestTask extends Task
{
    public function __construct()
    {
        parent::__construct(static fn (): int => 69);
    }
}
