<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class ComponentsIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     * @backupGlobals enabled
     */
    public function dumps_container_in_production(): void
    {
        $_ENV['ASYNC_DEBUG'] = $_SERVER['ASYNC_DEBUG'] = false;
        $_ENV['ASYNC_ENV'] = $_SERVER['ASYNC_ENV'] = 'prod';

        $components = TestKernel::create()->components;
        $components->compile();

        self::assertFileExists('/tmp/CompiledContainer.php', 'Container was not dumped');
    }
}
