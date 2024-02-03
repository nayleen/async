<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Recommender;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 * @backupGlobals enabled
 *
 * @covers \Nayleen\Async\Component\Recommender\Xdebug
 */
final class XdebugTest extends AsyncTestCase
{
    private function recommend(Kernel $kernel): void
    {
        (new Xdebug())->recommend($kernel);
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled(): void
    {
        $_ENV['XDEBUG_MODE'] = $_SERVER['XDEBUG_MODE'] = 'debug';

        $kernel = TestKernel::create();
        $this->recommend($kernel);

        self::assertTrue($kernel->log->hasNoticeThatContains('The "xdebug" extension is active, which has a major impact on performance'));
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled_in_ini_settings(): void
    {
        $_ENV['XDEBUG_MODE'] = $_SERVER['XDEBUG_MODE'] = null;

        $kernel = TestKernel::create();
        $this->recommend($kernel);

        self::assertTrue($kernel->log->hasNoticeThatContains('The "xdebug" extension is active, which has a major impact on performance'));
    }
}
