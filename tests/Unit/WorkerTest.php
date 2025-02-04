<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class WorkerTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function runs_in_kernel_context(): void
    {
        $kernel = new TestKernel();

        $worker = new Worker(static function (Kernel $kernel): void {
            $kernel->io()->info('Hi from your provided Kernel!');
        }, $kernel);

        $this->execute($worker, $kernel);
        self::assertTrue($kernel->log->hasInfoThatContains('Hi from your provided Kernel!'));
    }
}
