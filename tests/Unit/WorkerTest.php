<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\RuntimeTestCase;
use RuntimeException;

/**
 * @internal
 * @small
 */
final class WorkerTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function worker_returns_exitable_code(): void
    {
        $worker = new Worker(fn () => null);
        self::assertSame(0, $this->execute($worker));

        $worker = new Worker(fn () => throw new RuntimeException());
        self::assertSame(1, $this->execute($worker));
    }
}
