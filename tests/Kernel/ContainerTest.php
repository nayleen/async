<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @internal
 */
class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function promotes_itself_as_main_container(): void
    {
        $container = new Container($this->createStub(Kernel::class));

        self::assertTrue($container->has(ContainerInterface::class));
        self::assertSame($container, $container->get(ContainerInterface::class));
    }

    /**
     * @test
     */
    public function exposes_kernel_as_service(): void
    {
        $kernel = $this->createStub(Kernel::class);
        $container = new Container($kernel);

        self::assertTrue($container->has(Kernel::class));
        self::assertSame($kernel, $container->get(Kernel::class));
    }

    /**
     * @test
     */
    public function delegates_to_lookup_containers(): void
    {
        $delegateContainer1 = $this->createMock(ContainerInterface::class);
        $delegateContainer1->expects(self::once())->method('has')->willReturn(false);

        $delegateContainer2 = $this->createMock(ContainerInterface::class);
        $delegateContainer2->expects(self::once())->method('has')->willReturn(true);

        $container = new Container($this->createStub(Kernel::class));
        $container->add($delegateContainer1);
        $container->add($delegateContainer2);

        self::assertTrue($container->has(stdClass::class));
    }

    /**
     * @test
     */
    public function can_create_simple_services(): void
    {
        $container = new Container($this->createStub(Kernel::class));
        $instance = $container->make(stdClass::class);

        self::assertTrue($container->has(stdClass::class));
        self::assertSame($instance, $container->get(stdClass::class));

        // running make again returns the previous instance
        self::assertSame($instance, $container->make(stdClass::class));
    }

    /**
     * @test
     */
    public function throws_when_making_unknown_class(): void
    {
        $this->expectException(Exception::class);

        $container = new Container($this->createStub(Kernel::class));
        $container->make('This\\Class\\Does\\Not\\Exist');
    }

    /**
     * @test
     * @depends throws_when_making_unknown_class
     */
    public function can_return_previously_registered_unknown_class(): void
    {
        $className = 'This\\Class\\Does\\Not\\Exist';

        $container = new Container($this->createStub(Kernel::class));
        $container->set($className, new stdClass());

        $instance = $container->make($className);
        self::assertInstanceOf(stdClass::class, $instance);
    }

    /**
     * @test
     */
    public function registered_factory_marks_service_as_available(): void
    {
        $container = new Container($this->createStub(Kernel::class));
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
        $container = new Container($this->createStub(Kernel::class));
        $container->factory(stdClass::class, fn () => new stdClass());

        self::assertInstanceOf(stdClass::class, $container->get(stdClass::class));
    }

    /**
     * @test
     */
    public function setting_service_marks_it_as_available(): void
    {
        $container = new Container($this->createStub(Kernel::class));
        self::assertFalse($container->has(stdClass::class));

        $container->set(stdClass::class, new stdClass());
        self::assertTrue($container->has(stdClass::class));
    }

    /**
     * @test
     * @depends exposes_kernel_as_service
     */
    public function can_alias_existing_services(): void
    {
        $kernel = $this->createStub(Kernel::class);

        $container = new Container($kernel);
        self::assertFalse($container->has('async.kernel'));

        $container->alias('async.kernel', Kernel::class);
        self::assertTrue($container->has('async.kernel'));

        self::assertSame($kernel, $container->get('async.kernel'));
    }

    /**
     * @test
     */
    public function can_load_env_parameters(): void
    {
        putenv('APP_ENV=test');

        $container = new Container($this->createStub(Kernel::class));
        self::assertSame('test', $container->env('APP_ENV'));

        putenv('APP_ENV');
    }

    /**
     * @test
     */
    public function can_define_defaults_for_missing_env_parameters(): void
    {
        $container = new Container($this->createStub(Kernel::class));
        self::assertSame('fallback', $container->env('APP_ENV', 'fallback'));
    }

    /**
     * @test
     */
    public function throws_on_missing_env_parameter_without_default(): void
    {
        $this->expectException(Exception::class);

        $container = new Container($this->createStub(Kernel::class));
        $container->env('APP_ENV');
    }
}
