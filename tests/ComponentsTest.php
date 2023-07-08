<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI;
use DI\ContainerBuilder;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\HasDependencies;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class ComponentsTest extends TestCase
{
    private function makeDependentComponent(): Component
    {
        return new class() extends Component implements HasDependencies {
            public static function dependencies(): array
            {
                return [Bootstrapper::class];
            }

            public function name(): string
            {
                return 'test';
            }

            public function register(ContainerBuilder $containerBuilder): void
            {
            }

            public function shutdown(Kernel $kernel): void
            {
                $kernel->writeDebug('Shutting down Dependency');
            }
        };
    }

    /**
     * @test
     */
    public function adds_dependencies(): void
    {
        $component = $this->makeDependentComponent();
        $components = new Components([$component]);

        self::assertEquals([new Bootstrapper(), $component], iterator_to_array($components));
    }

    /**
     * @test
     */
    public function prevents_duplicates(): void
    {
        $component = new Bootstrapper();
        $components = new Components([$component, $component]);

        self::assertSame([$component], iterator_to_array($components));
    }

    /**
     * @test
     */
    public function shutdown_runs_shutdown_on_components(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler($testHandler = new TestHandler());

        $components = new Components([
            $this->makeDependentComponent(),
            DependencyProvider::create([
                'async.logger.factory' => DI\value(static fn (): LoggerInterface => $logger),
            ]),
        ]);

        $kernel = new Kernel($components);
        $components->shutdown($kernel);

        self::assertTrue($testHandler->hasDebugThatMatches('/Shutting down Kernel/'));
        self::assertTrue($testHandler->hasDebugThatMatches('/Shutting down Dependency/'));
    }
}
