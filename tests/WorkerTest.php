<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Kernel;
use Nayleen\Async\Worker\Worker as WorkerImplementation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

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
                    'logger.stderr' => new NullLogger(),
                    'logger.stdout' => new NullLogger(),
                ]),
            ],
        );
    }

    /**
     * @test
     */
    public function run_executes_worker(): void
    {
        $kernel = $this->createKernel();

        $worker = $this->createMock(WorkerImplementation::class);
        $worker->expects(self::once())->method('run');

        $runtime = new Worker($kernel, $worker);
        $runtime->run();
    }
}
