<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Kernel;
use Nayleen\Async\Worker as WorkerImplementation;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
class WorkerTest extends TestCase
{
    private EventLoop\Driver $loop;

    protected function setUp(): void
    {
        $this->loop = EventLoop::getDriver();
    }

    private function createKernel(?WorkerImplementation $worker = null): Kernel
    {
        return new Kernel(
            [
                DependencyProvider::create([
                    EventLoop\Driver::class => $this->loop,
                    'async.logger.stderr' => new NullLogger(),
                    'async.logger.stdout' => new NullLogger(),
                    WorkerImplementation::class => $worker,
                ]),
            ],
        );
    }

    /**
     * @test
     */
    public function can_instantiate_and_run_by_registered_class_name(): void
    {
        $worker = $this->createMock(WorkerImplementation::class);
        $worker->expects(self::once())->method('run');

        $runtime = new Worker(WorkerImplementation::class, $this->createKernel($worker));
        $runtime->run();
    }
}
