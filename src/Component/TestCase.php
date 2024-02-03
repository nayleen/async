<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Amp\PHPUnit\AsyncTestCase;
use DI\Container;
use Nayleen\Async\Component;
use Nayleen\Async\Test\TestKernel;

abstract class TestCase extends AsyncTestCase
{
    final protected function assertContainerHasParameter(string $name, ?string $type = null): void
    {
        $container = $this->container();

        self::assertTrue($container->has($name), sprintf('[%s] is not registered in the container', $name));

        if (isset($type)) {
            self::assertSame(
                $type,
                get_debug_type($container->get($name)),
                sprintf('[%s] is not of the expected type %s', $name, $type),
            );
        }
    }

    final protected function assertContainerHasService(string $name, ?string $type = null): void
    {
        $container = $this->container();

        self::assertTrue($container->has($name), sprintf('[%s] is not registered in the container', $name));

        $type ??= $name;
        assert(class_exists($type) || interface_exists($type));

        self::assertInstanceOf($type, $container->get($name));
    }

    abstract protected function component(): Component;

    final protected function container(): Container
    {
        static $container = null;

        return $container ??= (new TestKernel([$this->component()]))->components->compile();
    }
}
