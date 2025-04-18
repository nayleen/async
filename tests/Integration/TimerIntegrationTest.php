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
 */
final class TimerIntegrationTest extends AsyncTestCase
{
    private TestKernel $kernel;

    private EventLoop\Driver&MockObject $loop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loop = $this->createMock(EventLoop\Driver::class);
        $this->loop->method('defer')->willReturn('a');
        $this->loop->method('unreference')->willReturnArgument(0);

        $this->kernel = TestKernel::create([EventLoop\Driver::class => $this->loop]);
    }

    private function createTimer(): Timer
    {
        $timer = new readonly class() extends Timer {
            protected function execute(): void {}

            protected function interval(): float|int
            {
                return 0;
            }
        };

        $timer->start($this->kernel);

        return $timer;
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
    public function stop_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('cancel');

        $this->createTimer()->stop();
    }

    /**
     * @test
     */
    public function suspend_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');
        $this->loop->expects(self::once())->method('delay')->with(60, self::anything());

        $this->createTimer()->suspend(60);
    }
}
