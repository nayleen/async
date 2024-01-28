<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\PHPUnit\AsyncTestCase;
use InvalidArgumentException;
use Nayleen\Async\Test\NoopWorker;

/**
 * @internal
 */
final class ClusterTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function does_not_accept_stacked_clusters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cluster(new Cluster(new NoopWorker()));
    }
}
