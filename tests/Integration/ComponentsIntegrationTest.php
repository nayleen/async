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
        Environment::set('ASYNC_DEBUG', false);
        Environment::set('ASYNC_ENV', 'prod');

        $components = TestKernel::create()->components;
        $components->compile();

        self::assertFileExists('/tmp/CompiledContainer.php', 'Container was not dumped');
    }
}
