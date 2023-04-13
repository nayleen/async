<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command\Task;

use InvalidArgumentException;
use Nayleen\Async\Console\Command\Task;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @api
 */
final class Builder
{
    /**
     * @var array<int|string, string>
     */
    private array $arguments = [];

    /**
     * @var array<string, string>
     */
    private array $options = [
        '--no-interaction' => null,
    ];

    /**
     * @var array<string, string>
     */
    private array $shortcuts = [];

    public function __construct(private readonly string $command)
    {
    }

    public function argument(int|string $id, string $value): self
    {
        assert(!str_starts_with((string) $id, '-'), new InvalidArgumentException("Argument names don't contain any dashes"));

        $this->arguments[$id] = $value;

        return $this;
    }

    public function finalize(): Task
    {
        return new Task(
            $this->command,
            new ArrayInput(
                array_merge(
                    ['command' => $this->command],
                    $this->shortcuts,
                    $this->options,
                    $this->arguments,
                ),
            ),
        );
    }

    public function shortcut(string $name, string $value = null): self
    {
        assert(str_starts_with($name, '-'), new InvalidArgumentException('Short options start with a dash'));
        assert(!str_starts_with($name, '--'), new InvalidArgumentException('Short options start with only a single dash'));
        assert($value !== '', new InvalidArgumentException('Value must not be empty'));

        $this->shortcuts[$name] = $value;

        return $this;
    }

    public function option(string $name, string $value = null): self
    {
        assert(str_starts_with($name, '--'), new InvalidArgumentException('Options start with double dashes'));
        assert($value !== '', new InvalidArgumentException('Value must not be empty'));

        $this->options[$name] = $value;

        return $this;
    }
}
