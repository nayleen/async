<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container;

use Exception;
use Generator;
use Monolog\Logger;
use Nayleen\Async\Kernel\Container\Exception\AutowiringException;
use Nayleen\Async\Kernel\Container\Exception\CircularDependencyException;
use Nayleen\Async\Kernel\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * @internal
 * @group Container
 */
class ContainerAutowiringTest extends TestCase
{
    public function provideServicesToMake(): Generator
    {
        $container = new Container();
        $logger = $this->createStub(LoggerInterface::class);

        yield 'simple service automatically created' => [
            'container' => (clone $container),
            'make_params' => [stdClass::class],
            'assertion' => function (Container $container, stdClass $service)  {
                self::assertTrue($container->has(stdClass::class));
                self::assertSame($service, $container->get(stdClass::class));

                // running make again returns the previous instance
                self::assertSame($service, $container->make(stdClass::class));
            },
        ];

        yield 'simple service already known' => [
            'container' => (clone $container)->set(stdClass::class, new stdClass()),
            'make_params' => [stdClass::class],
            'assertion' => function (Container $container, stdClass $service)  {
                self::assertTrue($container->has(stdClass::class));
                self::assertSame($service, $container->get(stdClass::class));

                // running make again returns the previous instance
                self::assertSame($service, $container->make(stdClass::class));
            },
        ];

        yield ServiceWithUntypedProperty::class . ' with user provided dependency' => [
            'container' => (clone $container),
            'make_params' => [
                ServiceWithUntypedProperty::class,
                ['logger' => $logger],
            ],
            'assertion' => fn (Container $container, ServiceWithUntypedProperty $service) => self::assertSame($logger, $service->logger),
        ];

        yield ServiceWithLogger::class . ' with user provided dependency' => [
            'container' => (clone $container),
            'make_params' => [
                ServiceWithLogger::class,
                ['logger' => $logger],
            ],
            'assertion' => fn (Container $container, ServiceWithLogger $service) => self::assertSame($logger, $service->logger),
        ];

        yield ServiceWithLogger::class . ' with known dependency' => [
            'container' => (clone $container)->set(LoggerInterface::class, $logger),
            'make_params' => [
                ServiceWithLogger::class,
                [],
            ],
            'assertion' => fn (Container $container, ServiceWithLogger $service) => self::assertSame($logger, $service->logger),
        ];

        yield ServiceWithOptionalLogger::class . ' without dependency' => [
            'container' => (clone $container),
            'make_params' => [ServiceWithOptionalLogger::class],
            'assertion' => fn (Container $container, ServiceWithOptionalLogger $service) => self::assertNull($service->logger),
        ];

        yield ServiceWithOptionalLogger::class . ' with user provided dependency' => [
            'container' => (clone $container),
            'make_params' => [ServiceWithOptionalLogger::class, ['logger' => $logger]],
            'assertion' => fn (Container $container, ServiceWithOptionalLogger $service) => self::assertSame($logger, $service->logger),
        ];

        yield ServiceWithOptionalLogger::class . ' with known dependency' => [
            'container' => (clone $container)->set(LoggerInterface::class, $logger),
            'make_params' => [ServiceWithOptionalLogger::class],
            'assertion' => fn (Container $container, ServiceWithOptionalLogger $service) => self::assertSame($logger, $service->logger),
        ];

        yield ServiceWithRequiredPrimitive::class . ' with user provided dependency' => [
            'container' => (clone $container),
            'make_params' => [ServiceWithRequiredPrimitive::class, ['primitive' => 'test']],
            'assertion' => fn (Container $container, ServiceWithRequiredPrimitive $service) => self::assertSame('test', $service->primitive),
        ];

        yield ServiceWithOptionalPrimitive::class . ' without dependency' => [
            'container' => (clone $container),
            'make_params' => [ServiceWithOptionalPrimitive::class],
            'assertion' => fn (Container $container, ServiceWithOptionalPrimitive $service) => self::assertNull($service->primitive),
        ];

        yield ServiceWithLoggerAndRequiredPrimitive::class . ' with user provided dependency' => [
            'container' => (clone $container)->set(LoggerInterface::class, $logger),
            'make_params' => [ServiceWithLoggerAndRequiredPrimitive::class, ['primitive' => 'test']],
            'assertion' => function (Container $container, ServiceWithLoggerAndRequiredPrimitive $service) use ($logger) {
                self::assertSame($logger, $service->logger);
                self::assertSame('test', $service->primitive);
            },
        ];

        yield ServiceWithLoggerAndOptionalPrimitive::class . ' with user provided dependency' => [
            'container' => (clone $container)->set(LoggerInterface::class, $logger),
            'make_params' => [ServiceWithLoggerAndOptionalPrimitive::class, ['primitive' => 'test']],
            'assertion' => function (Container $container, ServiceWithLoggerAndOptionalPrimitive $service) use ($logger) {
                self::assertSame($logger, $service->logger);
                self::assertSame('test', $service->primitive);
            },
        ];
    }

    /**
     * @test
     * @dataProvider provideServicesToMake
     */
    public function can_create_service(Container $container, array $makeParams, callable $assertion): void
    {
        $assertion($container, $container->make(...$makeParams));
    }

    /**
     * @test
     */
    public function can_create_service_with_intersection_types(): void
    {
        $container = new Container();
        $container->set(LoggerInterface::class, $logger = new Logger('test'));

        $service = $container->make(ServiceWithIntersectionType::class, ['logger' => $logger]);
        self::assertSame($logger, $service->logger);

        $service = $container->make(ServiceWithIntersectionType::class);
        self::assertSame($logger, $service->logger);
    }

    /**
     * @test
     */
    public function can_create_service_with_intersection_types_concrete(): void
    {
        $container = new Container();
        $container->set(Logger::class, $logger = new Logger('test'));

        $service = $container->make(ServiceWithIntersectionType::class, ['logger' => $logger]);
        self::assertSame($logger, $service->logger);

        $service = $container->make(ServiceWithIntersectionType::class);
        self::assertSame($logger, $service->logger);
    }

    /**
     * @test
     */
    public function throws_on_missing_dependency(): void
    {
        $this->expectException(Exception::class);

        $container = new Container();
        $container->make(ServiceWithLogger::class);
    }

    /**
     * @test
     */
    public function throws_on_required_primitive(): void
    {
        $this->expectException(Exception::class);

        $container = new Container();
        $container->make(ServiceWithRequiredPrimitive::class);
    }

    /**
     * @test
     */
    public function throws_on_circular_dependency(): void
    {
        $this->expectException(CircularDependencyException::class);

        $container = new Container();
        $container->make(ClassWithCircularDependency::class);
    }

    /**
     * @test
     */
    public function throws_on_unresolvable_combined_type(): void
    {
        $this->expectException(AutowiringException::class);

        $container = new Container();
        $container->make(ServiceWithIntersectionType::class);
    }

    /**
     * @test
     */
    public function throws_on_private_constructor(): void
    {
        $this->expectException(AutowiringException::class);

        $container = new Container();
        $container->make(ServiceWithPrivateConstructor::class);
    }
}

/**
 * @internal
 */
class ServiceWithUntypedProperty
{
    public function __construct(public $logger)
    {

    }
}

/**
 * @internal
 */
class ServiceWithPrivateConstructor
{
    private function __construct()
    {

    }
}

/**
 * @internal
 */
class ServiceWithLogger
{
    public function __construct(public LoggerInterface $logger)
    {

    }
}

/**
 * @internal
 */
class ServiceWithIntersectionType
{
    public function __construct(public LoggerInterface&Logger $logger)
    {

    }
}

/**
 * @internal
 */
class ServiceWithOptionalLogger
{
    public function __construct(public ?LoggerInterface $logger = null)
    {

    }
}

/**
 * @internal
 */
class ServiceWithRequiredPrimitive
{
    public function __construct(public string $primitive)
    {

    }
}

/**
 * @internal
 */
class ServiceWithOptionalPrimitive
{
    public function __construct(public ?string $primitive = null)
    {

    }
}

/**
 * @internal
 */
class ServiceWithLoggerAndRequiredPrimitive
{
    public function __construct(public LoggerInterface $logger, public string $primitive)
    {

    }
}

/**
 * @internal
 */
class ServiceWithLoggerAndOptionalPrimitive
{
    public function __construct(public LoggerInterface $logger, public ?string $primitive = null)
    {

    }
}

/**
 * @internal
 */
class ClassWithCircularDependency
{
    public function __construct(private ClassCausingCircularDependency $circularDependency)
    {

    }
}

/**
 * @internal
 */
class ClassCausingCircularDependency
{
    public function __construct(private ClassWithCircularDependency $requiredService)
    {

    }
}
