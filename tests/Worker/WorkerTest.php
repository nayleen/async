<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Amp\Cancellation;
use Nayleen\Async\Timers;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;

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
        $loop = $this->createStub(EventLoop\Driver::class);

        $timers = $this->createMock(Timers::class);
        $timers->expects(self::once())->method('setup')->with($loop);

        $worker = new class() extends Worker {
            public function run(Cancellation $cancellation): void
            {
            }
        };
        $worker->attach($timers);
        $worker->setup($loop);
    }
}
