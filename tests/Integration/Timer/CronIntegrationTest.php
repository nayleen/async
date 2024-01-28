<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Monolog\Logger;
use Nayleen\Async\Clock;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;
use PHPUnit\Framework\MockObject\MockObject;
use Revolt\EventLoop;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 */
final class CronIntegrationTest extends AsyncTestCase
{
    private MockClock $clock;

    private Logger&MockObject $logger;

    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = new MockClock('1970-01-01 00:00:00');
        $this->logger = $this->createMock(Logger::class);

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->expects(self::once())->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createCron(Kernel $kernel): Cron
    {
        $cron = new readonly class($this->logger) extends Cron {
            public function __construct(private Logger $logger)
            {
                parent::__construct('* * * * *');
            }

            protected function execute(): void
            {
                static $invocations = 0;

                if ($invocations++ === 1) {
                    throw new CancelledException();
                }

                $this->logger->alert('Executing cron');
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
            ->withDependency(Clock::class, new Clock($this->loop, $this->clock))
            ->withDependency(Logger::class, $this->logger);

        $this->createCron($kernel)->run();
    }
}
