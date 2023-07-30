<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestComponent;

/**
 * @internal
 */
final class ComponentsTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function adds_dependencies(): void
    {
        $component = new TestComponent();
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
