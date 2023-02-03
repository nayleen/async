<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Nayleen\Async\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PerformanceTest extends TestCase
{
    /**
     * @test
     */
    public function logs_performance_recommendations_in_production_mode(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('prod');
        $kernel->expects(self::exactly(4))->method('write');

        Performance::recommend($kernel);
    }
}
