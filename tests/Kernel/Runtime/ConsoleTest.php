<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Runtime;

use Nayleen\Async\Kernel\Component\DependencyProvider;
use Nayleen\Async\Kernel\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ConsoleTest extends TestCase
{
    private readonly OutputInterface $output;

    protected function setUp(): void
    {
        $this->output = $this->createStub(OutputInterface::class);
    }

    private function createKernel(): Kernel
    {
        return new Kernel(
            [
                DependencyProvider::create([
                    EventLoop\Driver::class => EventLoop::getDriver(),
                    LoggerInterface::class => new NullLogger(),
                    OutputInterface::class => $this->output,
                ]),
            ]
        );
    }

    /**
     * @test
     */
    public function run_executes_console(): void
    {
        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('run')->with(null, $this->output);

        $runtime = new Console($this->createKernel(), $console, $this->output);
        $runtime->run();
    }

    /**
     * @test
     */
    public function passes_bound_io_to_console(): void
    {
        $input = $this->createStub(InputInterface::class);
        $output = $this->createStub(OutputInterface::class);

        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('run')->with($input, $output);

        $runtime = new Console($this->createKernel(), $console, $output);
        $runtime->input($input)->output($output)->run();
    }

    /**
     * @test
     */
    public function can_set_default_command(): void
    {
        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('setDefaultCommand')->with('test', true);

        $runtime = new Console($this->createKernel(), $console, $this->output);
        $runtime->command('test')->run();
    }

    /**
     * @test
     */
    public function can_set_default_command_with_instance(): void
    {
        $command = $this->createMock(Command::class);
        $command->method('getName')->willReturn('test');

        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('setDefaultCommand')->with('test', true);

        $runtime = new Console($this->createKernel(), $console, $this->output);
        $runtime->command($command)->run();
    }
}
