<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
class ContainerWrapperTest extends TestCase
{
    /**
     * @test
     */
    public function name_depends_on_container(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $expectedName = sprintf('container.%s', spl_object_hash($container));

        $component1 = ContainerWrapper::create($container);
        self::assertSame($expectedName, $component1->name());

        $component2 = ContainerWrapper::create($container);
        self::assertSame($component1->name(), $component2->name());
    }

    /**
     * @test
     */
    public function passes_container_to_container_builder(): void
    {
        $container = $this->createStub(ContainerInterface::class);

        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder->expects(self::once())->method('wrapContainer')->with($container);

        $component = ContainerWrapper::create($container);
        $component->register($containerBuilder);
    }
}
