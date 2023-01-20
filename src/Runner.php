<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runner;

use Amp\Cancellation;
use Amp\Parallel\Ipc;
use Amp\Parallel\Ipc\IpcHub;
use Amp\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

final class Runner
{
    private readonly IpcHub $ipcHub;

    private readonly string $script;

    private const CONSOLE_RUNNER_SCRIPT = __DIR__ . '/Internal/console-runner.php';

    private const SETTINGS_ASSERTIONS = [
        'assert.active' => '1',
        'assert.bail' => '0',
        'assert.exception' => '1',
        'assert.warning' => '0',
        'zend.assertions' => '1',
    ];

    private const SETTINGS_PHP = [
        'html_errors' => '0',
        'display_errors' => '0',
        'log_errors' => '1',
    ];

    private const SETTINGS_XDEBUG = [
        'xdebug.mode' => 'debug',
        'xdebug.start_with_request' => 'default',
        'xdebug.client_port' => '9003',
        'xdebug.client_host' => 'localhost',
    ];

    protected function __construct(
        private readonly Application $console,
        private readonly LoggerInterface $logger,
        ?IpcHub $ipcHub = null,
    ) {
        $this->ipcHub = $ipcHub ?? Ipc\ipcHub();
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
                    ...($this->settings($settings)),
                    $this->script,
                    $name,
                    $this->input($name, $options, $arguments),
                ],
            ),
        );
    }

    /**
     * @param array<string, string> $options
     * @param list<string> $arguments
     */
    private function input(string $command, array $options, array $arguments): string
    {
        return (string) new ArrayInput(
            $this->parameters($options, $arguments),
            $this->console->get($command)->getDefinition(),
        );
    }

    /**
     * @param array<string, string> $options
     * @param array<string, string>|list<string> $arguments
     * @return array<string, string>
     */
    private function parameters(array $options, array $arguments): array
    {
        $merged = [];
        foreach ($options as $name => $value) {
            if (!str_starts_with($name, '-')) {
                assert((function () use ($name) {
                    $this->logger->notice(
                        strtr(
                            "Prefixed option '%name' as --%name - to use short options, "
                            . 'pass them with option key formatted like this: `-%name`',
                            ['%name' => $name],
                        ),
                    );

                    return true;
                })());

                $name = "--$name";
            }

            $merged[$name] = $value;
        }

        return array_merge(
            $merged,
            array_map(trim(...), $arguments),
        );
    }

    /**
     * @param array<string, string> $settings
     * @return list<string>
     */
    private function settings(array $settings): array
    {
        $settings = array_replace(self::SETTINGS_PHP, $settings);

        // copy ini values set via the command line to the child process
        if (ini_get('zend.assertions') !== '-1') {
            foreach (self::SETTINGS_ASSERTIONS as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        if (ini_get('xdebug.mode') !== false) {
            foreach (self::SETTINGS_XDEBUG as $setting => $defaultValue) {
                $iniValue = ini_get($setting);
                $settings[$setting] = empty($iniValue) ? $defaultValue : $iniValue;
            }
        }

        ksort($settings, SORT_NATURAL);

        $result = [];
        foreach ($settings as $setting => $value) {
            $result[] = sprintf('-d%s=%s', $setting, $value);
        }

        return $result;
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
