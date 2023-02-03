<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Component\DependencyProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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

    private function createKernel(): Kernel
    {
        return new Kernel(
            new Components(
                [
                    DependencyProvider::create([
                        EventLoop\Driver::class => $this->loop,
                        'async.logger.stderr' => new NullLogger(),
                        'async.logger.stdout' => new NullLogger(),
                    ]),
                ],
            ),
        );
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

        $this->createTimer($this->createKernel())->disable();
    }

    /**
     * @test
     */
    public function enable_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('enable');

        $this->createTimer($this->createKernel())->enable();
    }

    /**
     * @test
     */
    public function stop_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::atLeast(1))->method('cancel');

        $this->createTimer($this->createKernel())->stop();
    }

    /**
     * @test
     */
    public function suspend_for_proxies_to_loop_driver(): void
    {
        $this->loop->expects(self::once())->method('disable');
        $this->loop->expects(self::once())->method('delay')->with(60, self::anything());

        $this->createTimer($this->createKernel())->suspendFor(60);
    }
}
