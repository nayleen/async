<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container;

use Nayleen\Async\Kernel\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @internal
 * @group Container
 */
class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function promotes_itself_as_main_container(): void
    {
        $container = new Container();

        self::assertTrue($container->has(ContainerInterface::class));
        self::assertSame($container, $container->get(ContainerInterface::class));
    }

    /**
     * @test
     */
    public function delegates_to_lookup_containers_for_existence_checks(): void
    {
        $delegateContainer1 = $this->createMock(ContainerInterface::class);
        $delegateContainer1->expects(self::once())->method('has')->willReturn(false);

        $delegateContainer2 = $this->createMock(ContainerInterface::class);
        $delegateContainer2->expects(self::once())->method('has')->willReturn(true);

        $container = new Container();
        $container->add($delegateContainer1);
        $container->add($delegateContainer2);

        self::assertTrue($container->has(stdClass::class));
    }

    /**
     * @test
     */
    public function delegates_to_lookup_containers_for_retrieval(): void
    {
        $delegateContainer1 = $this->createMock(ContainerInterface::class);
        $delegateContainer1->expects(self::once())->method('has')->willReturn(false);
        $delegateContainer1->expects(self::never())->method('get');

        $delegateContainer2 = $this->createMock(ContainerInterface::class);
        $delegateContainer2->expects(self::once())->method('has')->willReturn(true);
        $delegateContainer2->expects(self::once())->method('get')->willReturn(new stdClass());

        $container = new Container();
        $container->add($delegateContainer1);
        $container->add($delegateContainer2);

        self::assertInstanceOf(stdClass::class, $container->get(stdClass::class));
    }

    /**
     * @test
     */
    public function throws_when_making_unknown_class(): void
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->make('This\\Class\\Does\\Not\\Exist');
    }

    /**
     * @test
     * @depends throws_when_making_unknown_class
     */
    public function can_return_previously_registered_unknown_class(): void
    {
        $className = 'This\\Class\\Does\\Not\\Exist';

        $container = new Container();
        $container->set($className, new stdClass());

        $instance = $container->make($className);
        self::assertInstanceOf(stdClass::class, $instance);
    }

    /**
     * @test
     */
    public function registered_factory_marks_service_as_available(): void
    {
        $container = new Container();
        self::assertFalse($container->has(stdClass::class));

        $container->factory(stdClass::class, fn () => new stdClass());
        self::assertTrue($container->has(stdClass::class));
    }

    /**
     * @test
     * @depends registered_factory_marks_service_as_available
     */
    public function can_create_services_via_simple_factories(): void
    {
        $container = new Container();
        $container->factory(stdClass::class, fn () => new stdClass());

        self::assertInstanceOf(stdClass::class, $container->get(stdClass::class));
    }

    /**
     * @test
     */
    public function setting_service_marks_it_as_available(): void
    {
        $container = new Container();
        self::assertFalse($container->has(stdClass::class));

        $container->set(stdClass::class, new stdClass());
        self::assertTrue($container->has(stdClass::class));
    }

    /**
     * @test
     */
    public function can_alias_existing_services(): void
    {
        $container = new Container();
        $container->set(stdClass::class, $service = new stdClass());

        self::assertFalse($container->has('some_alias'));

        $container->alias('some_alias', stdClass::class);
        self::assertTrue($container->has('some_alias'));

        self::assertSame($service, $container->get('some_alias'));
    }

    /**
     * @test
     */
    public function can_load_env_parameters(): void
    {
        putenv('APP_ENV=test');

        $container = new Container();
        self::assertSame('test', $container->env('APP_ENV'));

        putenv('APP_ENV');
    }

    /**
     * @test
     */
    public function can_define_defaults_for_missing_env_parameters(): void
    {
        $container = new Container();
        self::assertSame('fallback', $container->env('APP_ENV', 'fallback'));
    }

    /**
     * @test
     */
    public function throws_on_missing_env_parameter_without_default(): void
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->env('APP_ENV');
    }
}
