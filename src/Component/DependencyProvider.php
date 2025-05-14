<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionSource;
use Nayleen\Async\Component;
use Override;

readonly class DependencyProvider extends Component
{
    /**
     * @var array<array-key, DefinitionSource|mixed[]|string>
     */
    private array $definitions; // @phpstan-ignore-line

    /**
     * @var non-empty-string
     */
    private string $name; // @phpstan-ignore-line

    private function __construct()
    {
        parent::__construct();
    }

    /**
     * @param DefinitionSource|mixed[]|string ...$definitions
     */
    public static function create(array|DefinitionSource|string ...$definitions): self
    {
        assert(func_num_args() > 0);

        $instance = new self();
        $instance->definitions = $definitions; // @phpstan-ignore-line
        $instance->name = 'dependencies.' . spl_object_hash($instance); // @phpstan-ignore-line

        return $instance;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        assert(isset($this->name));

        return $this->name;
    }

    #[Override]
    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions(...$this->definitions);
    }
}
