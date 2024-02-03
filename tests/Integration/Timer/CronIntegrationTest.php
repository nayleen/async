<?php

declare(strict_types = 1);

namespace Nayleen\Async\Timer;

use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Clock;
use Nayleen\Async\Test\TestKernel;
use Revolt\EventLoop;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 * @medium
 *
 * @covers \Nayleen\Async\Timer
 * @covers \Nayleen\Async\Timer\Cron
 */
final class CronIntegrationTest extends AsyncTestCase
{
    private function createCron(): Cron
    {
        return new readonly class() extends Cron {
            public function __construct()
            {
                parent::__construct('* * * * *');
            }

            protected function execute(): void
            {
                static $invocations = 0;

                if ($invocations++ === 1) {
                    throw new CancelledException();
                }

                $this->kernel->io()->alert('Executing cron');
            }
        };
    }

    /**
     * @test
     */
    public function delays_execution_according_to_schedule(): void
    {
        $loop = $this->createMock(EventLoop\Driver::class);
        $loop->expects(self::once())->method('defer')->willReturn('a');
        $loop->method('unreference')->willReturnArgument(0);

        $clock = new MockClock('1970-01-01 00:00:00');

        $loop->expects(self::once())->method('delay')->with(
            60,
            self::callback(static function () use ($clock) {
                $clock->modify('+60 seconds');

                return true;
            }),
        );

        $kernel = TestKernel::create($loop)->withDependency(Clock::class, new Clock($loop, $clock));
        $cron = $this->createCron();

        try {
            $cron->start($kernel);
            $cron->run();

            self::assertTrue($kernel->log->hasAlertThatContains('Executing cron'));
        } finally {
            $cron->stop();
        }
    }
}
