<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Amp\ByteStream\WritableBuffer;
use Amp\PHPUnit\AsyncTestCase;
use DI\Container;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Nayleen\Async\Bootstrapper;
use Nayleen\Async\Component;
use Nayleen\Async\Components;

abstract class TestCase extends AsyncTestCase
{
    private function container(): Container
    {
        $logger = new Logger('TestKernel');
        $logger->pushHandler(new NullHandler());

        $components = new Components([
            new Bootstrapper(),
            $this->component(),
            DependencyProvider::create([
                'async.stderr' => new WritableBuffer(),
                'async.stdout' => new WritableBuffer(),
                Logger::class => $logger,
            ]),
        ]);

        return $components->compile();
    }

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
}
