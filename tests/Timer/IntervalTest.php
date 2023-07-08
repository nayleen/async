<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Components;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Kernel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
final class IntervalTest extends TestCase
{
    private LoggerInterface&MockObject $logger;

    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->expects(self::once())->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createInterval(Kernel $kernel): Interval
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

                $this->logger->alert('Executing interval timer');
                $this->invocations++;
            }
        };

        $interval->start($kernel);

        return $interval;
    }

    private function createKernel(): Kernel
    {
        return new Kernel(
            new Components(
                [
                    DependencyProvider::create([
                        EventLoop\Driver::class => $this->loop,
                        LoggerInterface::class => $this->logger,
                        'async.logger.stderr' => new NullLogger(),
                        'async.logger.stdout' => $this->logger,
                    ]),
                ],
            ),
        );
    }

    /**
     * @test
     */
    public function delays_next_execution_by_interval(): void
    {
        try {
            $expectedDelay = 60;

            $this->loop->expects(self::once())->method('delay')->with($expectedDelay, self::anything());
            $this->logger->expects(self::once())->method('alert')->with('Executing interval timer');

            $this->createInterval($this->createKernel())->run();
        } catch (StopException) {
        }
    }
}
