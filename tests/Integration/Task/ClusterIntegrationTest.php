<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Nayleen\Async\Cluster;
use Nayleen\Async\Test\NoopWorker;
use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @medium
 *
 * @covers \Nayleen\Async\Cluster
 * @covers \Nayleen\Async\Runtime
 * @covers \Nayleen\Async\Worker
 * @covers \Nayleen\Async\Test\NoopWorker
 * @covers \Nayleen\Async\Test\TestKernel::trap()
 */
final class ClusterIntegrationTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function starts_specified_worker_count(): void
    {
        $kernel = TestKernel::create();

        $cluster = new Cluster(new NoopWorker(), 1);
        $this->execute($cluster, $kernel);

        self::assertTrue($kernel->log->hasInfoThatContains('Started cluster worker with ID 1'));
        self::assertTrue($kernel->log->hasInfoThatContains('Worker 1 terminated cleanly'));
    }
}
