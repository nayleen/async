<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class ConsoleTest extends AsyncTestCase
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
            input: $this->createStub(InputInterface::class),
            output: $this->createStub(OutputInterface::class),
        );
        $console->execute($kernel);
    }
}
