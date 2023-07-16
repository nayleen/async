<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;

/**
 * @internal
 */
final class WorkerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function sets_up_attached_timers(): void
    {
        $kernel = TestKernel::create();

        $timers = $this->createMock(Timers::class);
        $timers->expects(self::once())->method('start')->with($kernel);

        $worker = new NoopWorker($timers);
        $worker->run($kernel);
    }
}
