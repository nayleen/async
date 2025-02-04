<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class Loader implements CommandLoaderInterface
{
    /**
     * @var array<string, Command>
     */
    private array $map;

    public function __construct(
        private readonly Finder $finder,
        private readonly Container $container,
    ) {}

    private function find(): void
    {
        if (isset($this->map)) {
            return;
        }

        $map = [];
        foreach ($this->finder as $commandClass) {
            $command = $this->container->get($commandClass);
            assert($command instanceof Command);

            $map[$command->getName()] = $command;
        }

        $this->map = $map;
    }

    public function get(string $name): Command
    {
        $this->find();

        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        return $this->map[$name];
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        $this->find();

        return array_keys($this->map);
    }

    public function has(string $name): bool
    {
        $this->find();

        return isset($this->map[$name]);
    }
}
