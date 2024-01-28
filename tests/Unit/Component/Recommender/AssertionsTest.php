<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Recommender;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @backupGlobals enabled
 */
final class AssertionsTest extends AsyncTestCase
{
    private function recommend(Kernel $kernel): void
    {
        (new Assertions())->recommend($kernel);
    }

    /**
     * @test
     */
    public function logs_performance_implications_of_assertions(): void
    {
        $kernel = TestKernel::create();
        $this->recommend($kernel);

        self::assertTrue($kernel->log->hasNoticeThatContains('Running Nayleen\Async\Kernel with assertions enabled is not recommended'));
    }
}
