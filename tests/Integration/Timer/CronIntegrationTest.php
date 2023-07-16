<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Clock;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Kernel;
use Nayleen\Async\TestKernel;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 */
final class CronIntegrationTest extends AsyncTestCase
{
    private MockClock $clock;

    private LoggerInterface&MockObject $logger;

    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = new MockClock('1970-01-01 00:00:00');
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->expects(self::once())->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createCron(Kernel $kernel): Cron
    {
        $cron = new class($this->logger) extends Cron {
            private int $invocations = 0;

            public function __construct(private readonly LoggerInterface $logger)
            {
                parent::__construct('* * * * *');
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

        $cron->start($kernel);

        return $cron;
    }

    /**
     * @test
     */
    public function delays_execution_according_to_schedule(): void
    {
        try {
            $expectedDelay = 60;

            $this->loop->expects(self::once())->method('delay')->with(
                $expectedDelay,
                self::callback(function () {
                    $this->clock->modify('+60 seconds');

                    return true;
                }),
            );
            $this->logger->expects(self::once())->method('alert')->with('Executing cron');

            $kernel = TestKernel::create($this->loop)
                ->withDependency('async.logger', $this->logger)
                ->withDependency(Clock::class, new Clock($this->loop, $this->clock));

            $this->createCron($kernel)->run();
        } catch (StopException) {
        }
    }
}
