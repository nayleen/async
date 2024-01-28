<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\NoopWorker;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class ClusterIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function starts_specified_worker_count(): void
    {
        $kernel = TestKernel::create();

        $cluster = new Cluster(new NoopWorker(), 1);
        $cluster->kernel = $kernel;
        $cluster->run();

        self::assertTrue($kernel->log->hasInfoThatContains('Started cluster worker with ID 1'));
        self::assertTrue($kernel->log->hasInfoThatContains('Worker 1 terminated cleanly'));
    }
}
