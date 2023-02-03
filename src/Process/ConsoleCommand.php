<?php

declare(strict_types = 1);

namespace Nayleen\Async\Process;

use Amp\Parallel\Ipc\IpcHub;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

final class ConsoleCommand extends Runner
{

    protected function __construct(
        private readonly Application $console,
        private readonly LoggerInterface $logger,
        ?IpcHub $ipcHub = null,
    ) {
        $this->ipcHub = $ipcHub ?? Ipc\ipcHub();
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
}
