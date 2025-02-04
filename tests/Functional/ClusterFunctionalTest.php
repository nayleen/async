<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\NoopWorker;
use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @medium
 */
final class ClusterFunctionalTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function starts_specified_worker_count(): void
    {
        $kernel = new TestKernel();

        $cluster = new Cluster(new NoopWorker(), 1);
        $this->execute($cluster, $kernel);

        self::assertTrue($kernel->log->hasInfoThatContains('Started cluster worker with ID 1'));
    }
}
