<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class RuntimeTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function runs_in_kernel_context(): void
    {
        $kernel = TestKernel::create();

        $runtime = new readonly class($kernel) extends Runtime {
            public function __construct(Kernel $kernel)
            {
                parent::__construct(
                    fn (Kernel $kernel) => $kernel->io()->info('Hi from your provided Kernel!'),
                    $kernel,
                );
            }
        };

        $runtime->run();
        self::assertTrue($kernel->log->hasInfoThatContains('Hi from your provided Kernel!'));
    }
}
