<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Nayleen\Async\Test\Kernel as TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 */
final class RuntimeTest extends TestCase
{
    /**
     * @test
     */
    public function executes_console(): void
    {
        $exitCode = 420;

        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('run')->willReturn($exitCode);

        $kernel = TestKernel::create()->withDependency(Application::class, $console);

        $runtime = new Runtime(input: new ArrayInput([]), output: new NullOutput());
        $runtime = $runtime->withKernel($kernel);

        self::assertSame($exitCode, $runtime->run());
    }

    /**
     * @test
     */
    public function sets_default_command(): void
    {
        $defaultCommand = 'test';

        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('has')->with($defaultCommand)->willReturn(true);
        $console->expects(self::once())->method('setDefaultCommand')->with($defaultCommand, true);
        $console->expects(self::once())->method('run')->willReturn(0);

        $kernel = TestKernel::create()->withDependency(Application::class, $console);

        $runtime = new Runtime($defaultCommand, new ArrayInput([]), new NullOutput());
        $runtime = $runtime->withKernel($kernel);
        $runtime->run();
    }
}
