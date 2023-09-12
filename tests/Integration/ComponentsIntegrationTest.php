<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Safe;

/**
 * @internal
 */
final class ComponentsIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function dumps_container_in_production(): void
    {
        $originalDebugValue = getenv('ASYNC_DEBUG');
        Safe\putenv('ASYNC_DEBUG=false');
        $originalEnvValue = getenv('ASYNC_ENV');
        Safe\putenv('ASYNC_ENV=prod');

        $components = TestKernel::create()->components;
        $components->compile();

        self::assertTrue(file_exists('/tmp/CompiledContainer.php'), 'Container was not dumped');

        Safe\putenv('ASYNC_DEBUG=' . $originalDebugValue);
        Safe\putenv('ASYNC_ENV=' . $originalEnvValue);
    }
}
