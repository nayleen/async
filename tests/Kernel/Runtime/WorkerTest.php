<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Runtime;

use Nayleen\Async\Kernel\Component\DependencyProvider;
use Nayleen\Async\Kernel\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
use Nayleen\Async\Worker\Worker as WorkerImplementation;

/**
 * @internal
 */
class WorkerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->loop = EventLoop::getDriver();
    }

    private function createKernel(): Kernel
    {
        return new Kernel(
            [
                DependencyProvider::create([
                    EventLoop\Driver::class => $this->loop,
                    LoggerInterface::class => new NullLogger(),
                ]),
            ]
        );
    }

    /**
     * @test
     */
    public function run_executes_worker(): void
    {
        $worker = $this->createMock(WorkerImplementation::class);
        $worker->expects(self::once())->method('setup')->with($this->loop);
        $worker->expects(self::once())->method('run');

        $runtime = new Worker($this->createKernel(), $worker);
        $runtime->run();
    }
}
