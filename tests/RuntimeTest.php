<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\Kernel as TestKernel;
use Nayleen\Async\Test\Runtime as TestRuntime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RuntimeTest extends TestCase
{
    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $kernel = TestKernel::create();
        $runtime = new TestRuntime();
        ;

        self::assertSame(420, $runtime->withKernel($kernel)->run());
    }

    /**
     * @test
     */
    public function run_initializes_kernel(): void
    {
        $runtime = new TestRuntime();
        $runtime->run();

        self::assertTrue(isset($runtime->kernel));
    }
}
