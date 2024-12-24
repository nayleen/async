<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Advisory;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 * @backupGlobals enabled
 *
 * @covers \Nayleen\Async\Component\Advisory\Xdebug
 */
final class XdebugTest extends AsyncTestCase
{
    private function advise(Kernel $kernel, ?string $iniSetting = null): void
    {
        (new Xdebug($iniSetting))->advise($kernel);
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled(): void
    {
        $_ENV['XDEBUG_MODE'] = $_SERVER['XDEBUG_MODE'] = 'debug';

        $kernel = TestKernel::create();
        $this->advise($kernel);

        self::assertTrue($kernel->log->hasNoticeThatContains('The "xdebug" extension is active, which has a major impact on performance'));
    }

    /**
     * @test
     */
    public function logs_xdebug_being_enabled_in_ini_settings(): void
    {
        $_ENV['XDEBUG_MODE'] = $_SERVER['XDEBUG_MODE'] = null;

        $kernel = TestKernel::create();
        $this->advise($kernel, 'debug');

        self::assertTrue($kernel->log->hasNoticeThatContains('The "xdebug" extension is active, which has a major impact on performance'));
    }
}
