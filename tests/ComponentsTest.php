<?php

declare(strict_types = 1);

namespace Nayleen\Async\Unit;

use DI\ContainerBuilder;
use Nayleen\Async\Component;
use Nayleen\Async\Component\HasDependencies;
use Nayleen\Async\Components;
use Nayleen\Async\Kernel\Bootstrapper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ComponentsTest extends TestCase
{
    private function makeDependentComponent(): HasDependencies
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
}
