<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Nayleen\Async\Test\Kernel as TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ApplicationTest extends TestCase
{
    /**
     * @test
     */
    public function populates_name_and_version_from_container(): void
    {
        $kernel = TestKernel::create()
            ->withDependency('async.app_name', 'Test')
            ->withDependency('async.app_version', '13.3.7');

        $console = new Application($kernel);

        self::assertSame('Test', $console->getName());
        self::assertSame('13.3.7', $console->getVersion());
    }

    /**
     * @test
     */
    public function run_force_disables_auto_exit(): void
    {
        $kernel = TestKernel::create()
            ->withDependency('async.app_name', 'Test')
            ->withDependency('async.app_version', '13.3.7')
            ->withDependency(InputInterface::class, new ArrayInput([]))
            ->withDependency(OutputInterface::class, new NullOutput());

        $console = new Application($kernel);
        $exitCode = $console->run();

        self::assertFalse($console->isAutoExitEnabled());
        self::assertSame(0, $exitCode);
    }
}
