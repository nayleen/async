<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class KernelTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function always_prepends_bootstrapper(): void
    {
        $components = iterator_to_array(TestKernel::create()->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
    }

    /**
     * @test
     */
    public function bootstrapper_gets_deduplicated(): void
    {
        $kernel = new Kernel([new Bootstrapper()]);
        $components = iterator_to_array($kernel->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
        self::assertNotInstanceOf(Bootstrapper::class, $components[1]);
    }

    /**
     * @test
     */
    public function run_returns_value_from_callback(): void
    {
        $started = false;
        $return = TestKernel::create()->run(static function () use (&$started): int {
            $started = true;

            return 420;
        });

        self::assertSame(420, $return);
        self::assertTrue($started);
    }
}
