<?php

declare(strict_types = 1);

namespace Nayleen\Async\Recommender;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Safe;

/**
 * @internal
 */
final class PerformanceTest extends AsyncTestCase
{
    private string|false $originalXdebugEnvValue;

    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->originalXdebugEnvValue)) {
            Safe\putenv(
                'XDEBUG_MODE'
                . ($this->originalXdebugEnvValue === false
                    ? ''
                    : '=' . $this->originalXdebugEnvValue),
            );
        }
    }

    private function setXdebugMode(string $xdebugMode): void
    {
        $this->originalXdebugEnvValue = getenv('XDEBUG_MODE');
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
        $this->originalXdebugEnvValue = getenv('XDEBUG_MODE');
        Safe\putenv('XDEBUG_MODE');

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('environment')->willReturn('prod');
        $kernel->expects(self::exactly(4))->method('write');

        Performance::recommend($kernel);
    }
}
