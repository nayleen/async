<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Revolt\EventLoop;

/**
 * @internal
 * @medium
 *
 * @covers \Nayleen\Async\Timer
 * @covers \Nayleen\Async\Timer\Interval
 */
final class IntervalTest extends AsyncTestCase
{
    private function createInterval(): Interval
    {
        return new readonly class() extends Interval {
            public function __construct()
            {
                parent::__construct(60);
            }

            protected function execute(): void
            {
                static $invocations = 0;

                if ($invocations++ === 2) {
                    throw new CancelledException();
                }

                $this->kernel->io()->debug('Executing interval timer');
            }
        };
    }

    /**
     * @test
     */
    public function delays_next_execution_by_interval(): void
    {
        $loop = $this->createMock(EventLoop\Driver::class);
        $loop->expects(self::once())->method('defer')->willReturn('a');
        $loop->method('unreference')->willReturnArgument(0);

        $kernel = TestKernel::create($loop);
        $interval = $this->createInterval();

        $loop->expects(self::once())->method('delay')->with(60, self::callback(function () use ($interval) {
            $interval->enable();

            return true;
        }));

        try {
            $interval->start($kernel);
            $interval->run();

            self::assertTrue($kernel->log->hasDebugThatContains('Executing interval timer'));
        } finally {
            $interval->stop();
        }
    }
}
