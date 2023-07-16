<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;

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
