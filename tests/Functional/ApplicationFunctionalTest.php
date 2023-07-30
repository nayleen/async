<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestApplication;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class ApplicationFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $app = new TestApplication();
        $app->kernel = TestKernel::create();

        self::assertSame(420, $app->run());
    }
}
