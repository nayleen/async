<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Nayleen\Async\Kernel;
use PHPUnit\Framework\TestCase;
use Safe;

/**
 * @internal
 */
final class PerformanceTest extends TestCase
{
    private string|false $originalEnvValue;

    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->originalEnvValue)) {
            Safe\putenv('XDEBUG_MODE=' . $this->originalEnvValue);
        }
    }

    private function setXdebugMode(string $xdebugMode): void
    {
        $this->originalEnvValue = getenv('XDEBUG_MODE');
        Safe\putenv('XDEBUG_MODE=' . $xdebugMode);
    }

    /**
     * @test
     */
    public function logs_nothing_in_non_production_mode(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('dev');
        $kernel->expects(self::never())->method('write');

        Performance::recommend($kernel);
    }

    /**
     * @test
     */
    public function logs_only_performance_recommendations_in_production_mode(): void
    {
        $this->setXdebugMode('off');

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('prod');
        $kernel->expects(self::exactly(3))->method('write');

        Performance::recommend($kernel);
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled(): void
    {
        $this->setXdebugMode('debug');

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('prod');
        $kernel->expects(self::exactly(4))->method('write');

        Performance::recommend($kernel);
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled_in_ini_settings(): void
    {
        $this->originalEnvValue = getenv('XDEBUG_MODE');
        Safe\putenv('XDEBUG_MODE');

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('prod');
        $kernel->expects(self::exactly(4))->method('write');

        Performance::recommend($kernel);
    }
}
