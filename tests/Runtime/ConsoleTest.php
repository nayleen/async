<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Kernel;
use PHPUnit\Framework\TestCase;
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
                    'async.logger.stderr' => new NullLogger(),
                    'async.logger.stdout' => new NullLogger(),
                    OutputInterface::class => $this->output,
                ]),
            ],
        );
    }

    /**
     * @test
     */
    public function can_set_default_command(): void
    {
        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('has')->with('test')->willReturn(true);
        $console->expects(self::once())->method('setDefaultCommand')->with('test', true);

        $runtime = new Console($this->createKernel(), $console);
        $runtime->command('test')->run($this->output);
    }

    /**
     * @test
     */
    public function can_set_default_command_with_instance(): void
    {
        $command = $this->createMock(Command::class);
        $command->method('getName')->willReturn('test');

        $console = $this->createMock(Application::class);
        $console->expects(self::exactly(2))->method('has')->with('test')->willReturnOnConsecutiveCalls(false, true);
        $console->expects(self::once())->method('setDefaultCommand')->with('test', true);

        $runtime = new Console($this->createKernel(), $console);
        $runtime->command($command)->run($this->output);
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

        $runtime = new Console($this->createKernel(), $console);
        $runtime->run($output, $input);
    }

    /**
     * @test
     */
    public function run_executes_console(): void
    {
        $console = $this->createMock(Application::class);
        $console->expects(self::once())->method('run')->with($this->anything(), $this->output)->willReturn(0);

        $runtime = new Console($this->createKernel(), $console);
        $runtime->run($this->output);
    }
}
