<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Amp\Cancellation;
use Nayleen\Async\Kernel;
use Nayleen\Async\Timers;
use Nayleen\Async\Worker;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class WorkerTest extends TestCase
{
    /**
     * @test
     */
    public function sets_up_attached_timers(): void
    {
        $kernel = $this->createStub(Kernel::class);

        $timers = $this->createMock(Timers::class);
        $timers->expects(self::once())->method('start')->with($kernel);

        $worker = new class($timers) extends Worker {
            protected function execute(Cancellation $cancellation): void
            {
            }
        };
        $worker->run($kernel);
    }
}
