<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use DateTimeImmutable;
use DateTimeZone;
use Revolt\EventLoop;
use Symfony\Component\Clock\ClockInterface;

/**
 * @internal
 * @small
 */
final class ClockTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_overwrite_timezone(): void
    {
        $timezone = self::createStub(DateTimeZone::class);

        $wrappedClock = $this->createMock(ClockInterface::class);
        $wrappedClock->expects(self::once())->method('withTimeZone')->with($timezone);

        $clock = new Clock(self::createStub(EventLoop\Driver::class), $wrappedClock);
        $clock = $clock->withTimeZone($timezone);

        self::assertSame($timezone, $clock->timezone());
    }

    /**
     * @test
     */
    public function returns_current_time_from_wrapped_clock(): void
    {
        $now = self::createStub(DateTimeImmutable::class);

        $wrappedClock = $this->createMock(ClockInterface::class);
        $wrappedClock->expects(self::once())->method('now')->willReturn($now);

        $clock = new Clock(self::createStub(EventLoop\Driver::class), $wrappedClock);

        self::assertSame($now, $clock->now());
    }

    /**
     * @test
     */
    public function sleep_is_implemented_as_async_delay(): void
    {
        $suspension = $this->createMock(EventLoop\Suspension::class);
        $suspension->expects(self::once())->method('suspend');
        $suspension->expects(self::once())->method('resume');

        $loop = $this->createMock(EventLoop\Driver::class);
        $loop->expects(self::once())->method('getSuspension')->willReturn($suspension);
        $loop->expects(self::once())->method('delay')->willReturnCallback(function () use (&$suspension) {
            $suspension->resume();

            return 'a';
        });

        $loop->expects(self::once())->method('unreference')->with('a')->willReturnArgument(0);
        $loop->expects(self::once())->method('cancel')->with('a');

        $clock = new Clock($loop);
        $clock->sleep(1);
    }

    /**
     * @test
     */
    public function sleep_noops_on_lte_zero_seconds(): void
    {
        $loop = $this->createMock(EventLoop\Driver::class);
        $loop->expects(self::never())->method('getSuspension');

        $clock = new Clock($loop);
        $clock->sleep(0);
    }

    /**
     * @test
     */
    public function uses_system_timezone_by_default(): void
    {
        $clock = new Clock(self::createStub(EventLoop\Driver::class));

        self::assertSame(date_default_timezone_get(), $clock->timezone()->getName());
    }
}
