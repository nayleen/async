<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Advisory;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 * @backupGlobals enabled
 */
final class AssertionsTest extends AsyncTestCase
{
    private function advise(Kernel $kernel): void
    {
        (new Assertions())->advise($kernel);
    }

    /**
     * @test
     */
    public function logs_performance_implications_of_assertions(): void
    {
        $kernel = new TestKernel();
        $this->advise($kernel);

        self::assertTrue($kernel->log->hasNoticeThatContains('Running Nayleen\Async\Kernel with assertions enabled is not recommended'));
    }
}
