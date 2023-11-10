<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionSource;
use Nayleen\Async\Component;

class DependencyProvider extends Component
{
    /**
     * @var array<array-key, DefinitionSource|mixed[]|string>
     */
    private array $definitions;

    /**
     * @return non-empty-string
     */
    private string $name;

    /**
     * @param DefinitionSource|mixed[]|string ...$definitions
     */
    public static function create(array|DefinitionSource|string ...$definitions): self
    {
        assert(func_num_args() > 0);

        $instance = new self();
        $instance->definitions = $definitions;
        $instance->name = uniqid('dependencies.', true);

        return $instance;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        assert($this->name !== '');

        return $this->name;
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions(...$this->definitions);
    }
}
