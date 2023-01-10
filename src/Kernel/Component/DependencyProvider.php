<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionSource;

abstract class DependencyProvider extends Component
{
    /**
     * @var string|array|DefinitionSource[]
     */
    private readonly array $definitions;

    private readonly string $name;

    public static function create(string|array|DefinitionSource ...$definitions): self
    {
        assert(!empty($definitions));

        $instance = new class extends DependencyProvider {};
        $instance->definitions = $definitions;
        $instance->name = uniqid('dependencies.', true);

        return $instance;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions(...$this->definitions);
    }
}
