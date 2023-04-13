<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;
use Symfony\Component\Clock\ClockInterface;

/**
 * @internal
 */
class ClockTest extends TestCase
{
    /**
     * @test
     */
    public function can_overwrite_timezone(): void
    {
        $timeZone = $this->createStub(DateTimeZone::class);

        $wrappedClock = $this->createMock(ClockInterface::class);
        $wrappedClock->expects(self::once())->method('withTimeZone')->with($timeZone);

        $clock = new Clock($this->createStub(EventLoop\Driver::class), $wrappedClock);
        $clock = $clock->withTimeZone($timeZone);

        self::assertSame($timeZone, $clock->timezone());
    }

    /**
     * @test
     */
    public function returns_current_time_from_wrapped_clock(): void
    {
        $now = $this->createStub(DateTimeImmutable::class);

        $wrappedClock = $this->createMock(ClockInterface::class);
        $wrappedClock->expects(self::once())->method('now')->willReturn($now);

        $clock = new Clock($this->createStub(EventLoop\Driver::class), $wrappedClock);

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
        $clock->sleep(0);
    }

    /**
     * @test
     */
    public function uses_system_timezone_by_default(): void
    {
        $clock = new Clock($this->createStub(EventLoop\Driver::class));

        self::assertSame(date_default_timezone_get(), $clock->timezone()->getName());
    }
}
