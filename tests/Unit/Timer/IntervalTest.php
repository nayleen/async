<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Monolog\Logger;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;
use PHPUnit\Framework\MockObject\MockObject;
use Revolt\EventLoop;

/**
 * @internal
 */
final class IntervalTest extends AsyncTestCase
{
    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->expects(self::once())->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createInterval(Kernel $kernel): Interval
    {
        $interval = new readonly class($kernel->container()->get(Logger::class)) extends Interval {
            public function __construct(private Logger $logger)
            {
                parent::__construct(60);
            }

            protected function execute(): void
            {
                static $invocations = 0;

                if ($invocations++ === 1) {
                    throw new CancelledException();
                }

                $this->logger->alert('Executing interval timer');
            }
        };

        $interval->start($kernel);

        return $interval;
    }

    /**
     * @test
     */
    public function delays_next_execution_by_interval(): void
    {
        $expectedDelay = 60;

        $this->loop->expects(self::once())->method('delay')->with($expectedDelay, self::anything());

        $kernel = TestKernel::create($this->loop);

        $this->createInterval($kernel)->run();

        self::assertTrue($kernel->log->hasAlertThatContains('Executing interval timer'));
    }
}
