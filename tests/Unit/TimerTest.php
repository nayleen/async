<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use PHPUnit\Framework\MockObject\MockObject;
use Revolt\EventLoop;

/**
 * @internal
 */
final class TimerTest extends AsyncTestCase
{
    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);
    }

    private function createTimer(Kernel $kernel): Timer
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

        $timer->start($kernel);

        return $timer;
    }

    /**
     * @test
     */
    public function disable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');

        $this->createTimer(TestKernel::create($this->loop))->disable();
    }

    /**
     * @test
     */
    public function enable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('enable');

        $this->createTimer(TestKernel::create($this->loop))->enable();
    }

    /**
     * @test
     */
    public function stop_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::atLeast(1))->method('cancel');

        $this->createTimer(TestKernel::create($this->loop))->stop();
    }

    /**
     * @test
     */
    public function suspend_for_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');
        $this->loop->expects(self::once())->method('delay')->with(60, self::anything());

        $this->createTimer(TestKernel::create($this->loop))->suspend(60);
    }
}
