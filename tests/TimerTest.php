<?php

declare(strict_types = 1);

namespace Nayleen\Async\Unit;

use Nayleen\Async\Timer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;

/**
 * @internal
 */
class TimerTest extends TestCase
{
    private EventLoop\Driver|MockObject $loop;

    protected function setUp(): void
    {
        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createTimer(): Timer
    {
        $timer = new class() extends Timer {
            protected function execute(): void
            {
            }

            protected function interval(): float|int
            {
                return 0;
            }
        };

        return $timer->setup($this->loop);
    }

    /**
     * @test
     */
    public function cancel_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::atLeast(1))->method('cancel');

        $this->createTimer()->cancel();
    }

    /**
     * @test
     */
    public function disable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');

        $this->createTimer()->disable();
    }

    /**
     * @test
     */
    public function enable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('enable');

        $this->createTimer()->enable();
    }

    /**
     * @test
     */
    public function suspend_for_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');
        $this->loop->expects(self::once())->method('delay')->with(60, self::anything());

        $this->createTimer()->suspendFor(60);
    }
}
