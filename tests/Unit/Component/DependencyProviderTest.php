<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Amp\PHPUnit\AsyncTestCase;
use DI;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Component\DependencyProvider
 */
final class DependencyProviderTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function name_is_automatically_generated(): void
    {
        $component1 = DependencyProvider::create([]);
        self::assertStringStartsWith('dependencies.', $component1->name());

        $component2 = DependencyProvider::create([]);
        self::assertNotSame($component1->name(), $component2->name());
    }

    /**
     * @test
     */
    public function passes_given_definitions_to_container_builder(): void
    {
        $definitions = [];

        $containerBuilder = $this->createMock(DI\ContainerBuilder::class);
        $containerBuilder->expects(self::once())->method('addDefinitions')->with(...$definitions);

        $component = DependencyProvider::create($definitions);
        $component->register($containerBuilder);
    }
}
