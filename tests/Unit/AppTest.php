<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\NoopWorker;
use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class AppTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function addTimer_attaches_timer_to_collection(): void
    {
        $kernel = new TestKernel();

        $timers = $this->createMock(App\Timers::class);
        $timers->expects(self::once())->method('add')->with(self::isInstanceOf(Timer::class));

        $app = new App($kernel, timers: $timers);
        $app->addTimer(new readonly class() extends Timer {
            protected function execute(): void {}

            protected function interval(): float|int
            {
                return 0;
            }
        });
    }

    /**
     * @test
     */
    public function addTimer_can_retrieve_container_reference(): void
    {
        $kernel = TestKernel::create(['custom_timer' => new readonly class extends Timer {
            protected function execute(): void {}

            protected function interval(): float|int
            {
                return 0;
            }
        }]);

        $timers = $this->createMock(App\Timers::class);
        $timers->expects(self::once())->method('add')->with(self::isInstanceOf(Timer::class));

        $app = new App($kernel, timers: $timers);
        $app->addTimer('custom_timer');
    }

    /**
     * @test
     */
    public function addWorker_attaches_worker_to_collection(): void
    {
        $kernel = new TestKernel();

        $workers = $this->createMock(App\Workers::class);
        $workers->expects(self::once())->method('add')->with(self::isInstanceOf(NoopWorker::class));

        $app = new App($kernel, workers: $workers);
        $app->addWorker(new NoopWorker());
    }

    /**
     * @test
     */
    public function addWorker_can_retrieve_container_reference(): void
    {
        $kernel = new TestKernel();

        $workers = $this->createMock(App\Workers::class);
        $workers->expects(self::once())->method('add')->with(self::isInstanceOf(NoopWorker::class));

        $app = new App($kernel, workers: $workers);
        $app->addWorker(NoopWorker::class);
    }

    /**
     * @test
     */
    public function running_app_starts_timers_and_workers(): void
    {
        $kernel = new TestKernel();

        $timers = $this->createMock(App\Timers::class);
        $timers->expects(self::once())->method('start')->with($kernel);

        $workers = $this->createMock(App\Workers::class);
        $workers->expects(self::once())->method('start')->with($kernel);

        $app = new App($kernel, timers: $timers, workers: $workers);
        $app->run();
    }
}
