<?php

declare(strict_types = 1);

namespace Nayleen\Async\Unit;

use Nayleen\Async\Timer\Cron;
use Nayleen\Async\Timer\Interval;
use Nayleen\Async\Timers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 */
class TimersTest extends TestCase
{
    private Cron|MockObject $cron;

    private Interval|MockObject $interval;

    protected function setUp(): void
    {
        $this->cron = $this->createMock(Cron::class);
        $this->interval = $this->createMock(Interval::class);
    }

    private function createTimers(): Timers
    {
        $timers = new Timers($this->cron, $this->interval);
        $timers = $timers->withClock(new MockClock());
        $timers->setup($this->createStub(EventLoop\Driver::class));

        return $timers;
    }

    /**
     * @test
     */
    public function cancel_proxies_to_timers(): void
    {
        $this->cron->expects(self::atLeast(1))->method('cancel'); // also called during destructor
        $this->interval->expects(self::atLeast(1))->method('cancel'); // also called during destructor

        $this->createTimers()->cancel();
    }

    /**
     * @test
     */
    public function disable_proxies_to_timers(): void
    {
        $this->cron->expects(self::once())->method('disable');
        $this->interval->expects(self::once())->method('disable');

        $this->createTimers()->disable();
    }

    /**
     * @test
     */
    public function enable_proxies_to_timers(): void
    {
        $this->cron->expects(self::once())->method('enable');
        $this->interval->expects(self::once())->method('enable');

        $this->createTimers()->enable();
    }

    /**
     * @test
     */
    public function suspend_for_proxies_to_timers(): void
    {
        $this->cron->expects(self::once())->method('suspendFor')->with(60);
        $this->interval->expects(self::once())->method('suspendFor')->with(60);

        $this->createTimers()->suspendFor(60);
    }
}
