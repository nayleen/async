<?php

declare(strict_types = 1);

namespace Nayleen\Async\Process;

use Amp\Cancellation;
use Amp\Process;
use Symfony\Component\Console\Input\ArrayInput;

abstract class Runner
{
    public function __construct(private readonly string $script)
    {

    }

    /**
     * @param array<string, string> $settings
     * @param array<string, string> $options
     * @param list<string> $arguments
     * @return list<string>
     */
    private function command(array $settings, string $name, array $options, array $arguments): array
    {
        return array_values(
            array_filter(
                [
                    PHP_BINARY,
                    ...(CommandLineSettings::apply($settings)),
                    $this->script,
                    $name,
                    $this->input($name, $options, $arguments),
                ],
            ),
        );
    }

    /**
     * @param array<string, array<string, mixed>|mixed> $options
     * @param list<string> $arguments
     * @param array<string, string> $environment
     */
    public function run(
        string $command,
        array $options = [],
        array $arguments = [],
        array $environment = [],
        array $settings = [],
        ?Cancellation $cancellation = null,
    ): Process\Process {
        return Process\Process::start(
            $this->command($settings, $command, $options, $arguments),
            environment: $environment,
            cancellation: $cancellation,
        );
    }
}
