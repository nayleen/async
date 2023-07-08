<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Timer\Cron;
use Nayleen\Async\Timer\Interval;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TimersTest extends TestCase
{
    private Cron&MockObject $cron;

    private Interval&MockObject $interval;

    private Kernel&MockObject $kernel;

    protected function setUp(): void
    {
        $this->cron = $this->createMock(Cron::class);
        $this->interval = $this->createMock(Interval::class);
        $this->kernel = $this->createMock(Kernel::class);
    }

    private function createTimers(): Timers
    {
        $timers = new Timers($this->cron, $this->interval);
        $timers->start($this->kernel);

        return $timers;
    }

    /**
     * @test
     */
    public function cancel_proxies_to_timers(): void
    {
        $this->cron->expects(self::atLeast(1))->method('stop'); // also called during destructor
        $this->interval->expects(self::atLeast(1))->method('stop'); // also called during destructor

        $this->createTimers()->stop();
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
