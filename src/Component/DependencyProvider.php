<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionSource;
use Nayleen\Async\Component;

/**
 * @api
 */
abstract class DependencyProvider extends Component
{
    /**
     * @var array<array-key, array|DefinitionSource|string>
     */
    private readonly array $definitions;

    /**
     * @return non-empty-string
     */
    private readonly string $name;

    public static function create(string|array|DefinitionSource ...$definitions): self
    {
        assert(!empty($definitions));

        $instance = new class() extends DependencyProvider {
        };

        /**
         * @psalm-suppress UndefinedPropertyAssignment
         */
        $instance->definitions = $definitions;

        /**
         * @psalm-suppress UndefinedPropertyAssignment
         */
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
