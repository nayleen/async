<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class PerformanceRecommenderTest extends TestCase
{
    /**
     * @test
     */
    public function logs_performance_recommendations_in_production_mode(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('notice');

        PerformanceRecommender::execute($logger, 'prod');
    }
}
