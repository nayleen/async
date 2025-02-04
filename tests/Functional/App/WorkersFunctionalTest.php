<?php

declare(strict_types = 1);

namespace Nayleen\Async\App;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Worker;

use function Amp\delay;

/**
 * @internal
 * @large
 */
final class WorkersFunctionalTest extends AsyncTestCase
{
    private function createWorkers(): Workers
    {
        $workers = new Workers();

        $workers->add(new Worker(static function (): void {
            delay(1);
        }));

        $workers->add(new Worker(static function (): void {
            delay(1);
        }));

        return $workers;
    }

    /**
     * @test
     */
    public function starts_watchers_for_workers(): void
    {
        $workers = $this->createWorkers();
        $workers->start(new TestKernel());

        foreach ($workers->workers as $worker) {
            self::assertTrue($workers->watchers->contains($worker));
        }
    }
}
