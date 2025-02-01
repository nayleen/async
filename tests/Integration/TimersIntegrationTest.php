<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use PHPUnit\Framework\MockObject\MockObject;
use Revolt\EventLoop;

/**
 * @internal
 * @medium
 *
 * @covers \Nayleen\Async\Timers
 */
final class TimersIntegrationTest extends AsyncTestCase
{
    private TestKernel $kernel;

    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);

        $this->kernel = TestKernel::create($this->loop);
    }

    private function createTimer(): Timer
    {
        return new readonly class() extends Timer {
            protected function execute(): void {}

            protected function interval(): float|int
            {
                return 0;
            }
        };
    }

    private function createTimers(): Timers
    {
        $timers = new Timers();
        $timers->add($this->createTimer());
        $timers->start($this->kernel);

        return $timers;
    }

    /**
     * @test
     */
    public function disable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');

        $this->createTimers()->disable();
    }

    /**
     * @test
     */
    public function enable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('enable');

        $this->createTimers()->enable();
    }

    /**
     * @test
     */
    public function stop_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('cancel');

        $this->createTimers()->stop();
    }

    /**
     * @test
     */
    public function suspend_for_proxies_to_timers(): void
    {
        $this->loop->expects(self::once())->method('disable');
        $this->loop->expects(self::once())->method('delay')->with(60, self::anything());

        $this->createTimers()->suspend(60);
    }
}
