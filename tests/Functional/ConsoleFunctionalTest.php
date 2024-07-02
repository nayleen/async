<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
final class ConsoleFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $console = new Console(
            defaultCommand: 'list',
            input: new ArrayInput([]),
            output: $output = new BufferedOutput(),
        );

        self::assertSame(0, $console->execute(TestKernel::create()));
        self::assertStringContainsString('Available commands:', $output->fetch());
    }
}
