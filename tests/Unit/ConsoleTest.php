<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestKernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ConsoleTest extends RuntimeTestCase
{
    /**
     * @test
     */
    public function runs_symfony_console(): void
    {
        $consoleApplication = $this->createMock(Application::class);
        $consoleApplication->expects(self::once())->method('run');

        $kernel = TestKernel::create()->withDependency(Application::class, $consoleApplication);

        $console = new Console(
            input: self::createStub(InputInterface::class),
            output: self::createStub(OutputInterface::class),
        );
        $this->execute($console, $kernel);
    }
}
