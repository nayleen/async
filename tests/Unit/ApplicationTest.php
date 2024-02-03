<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestApplication;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Application
 * @covers \Nayleen\Async\Test\TestApplication
 */
final class ApplicationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        self::assertSame(420, (new TestApplication())->execute(TestKernel::create()));
    }
}
