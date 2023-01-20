<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Nayleen\Async\Kernel\Exception\StopException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

/**
 * @internal
 */
class IntervalTest extends TestCase
{
    private LoggerInterface|MockObject $logger;

    private EventLoop\Driver|MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->expects(self::once())->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createInterval(): Interval
    {
        $interval = new class($this->logger) extends Interval {
            private int $invocations = 0;

            public function __construct(
                private readonly LoggerInterface $logger,
            ) {
                parent::__construct(60);
            }

            protected function execute(): void
            {
                if ($this->invocations === 1) {
                    throw new StopException();
                }

                $this->logger->alert('Executing cron');
                $this->invocations++;
            }
        };

        return $interval->setup($this->loop);
    }

    /**
     * @test
     */
    public function delays_next_execution_by_interval(): void
    {
        try {
            $expectedDelay = 60;

            $this->loop->expects(self::exactly(2))->method('delay')->with($expectedDelay, self::anything());
            $this->logger->expects(self::once())->method('alert');

            $this->createInterval()->run();
        } catch (StopException) {
        }
    }
}
