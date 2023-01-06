<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use DI\ContainerBuilder;
use Nayleen\Async\Kernel\Bootstrapper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
class ComponentsTest extends TestCase
{
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
    public function adds_dependencies(): void
    {
        $component = $this->makeDependentComponent();
        $components = new Components([$component]);

        self::assertEquals([new Bootstrapper(), $component], iterator_to_array($components));
    }

    private function makeDependentComponent(): HasDependencies
    {
        return new class extends Component implements HasDependencies {
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
        };
    }
}
