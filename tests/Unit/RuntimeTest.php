<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use AssertionError;
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
        $kernel = new TestKernel();

        $runtime = new readonly class($kernel) extends Runtime {
            public function __construct(Kernel $kernel)
            {
                parent::__construct(static function (Kernel $kernel): void {
                    $kernel->io()->info('Hi from your provided Kernel!');
                }, $kernel);
            }
        };

        $runtime->run();
        self::assertTrue($kernel->log->hasInfoThatContains('Hi from your provided Kernel!'));
    }

    /**
     * @test
     */
    public function runtime_validates_closure_parameters(): void
    {
        $kernel = new TestKernel();

        // valid - no parameters
        new readonly class($kernel) extends Runtime {
            public function __construct(Kernel $kernel)
            {
                parent::__construct(static function (): void {}, $kernel);
            }
        };

        // valid - Kernel parameter
        new readonly class($kernel) extends Runtime {
            public function __construct(Kernel $kernel)
            {
                parent::__construct(static function (Kernel $kernel): void {}, $kernel);
            }
        };

        // invalid - parameter type mismatch
        $this->expectException(AssertionError::class);

        new readonly class($kernel) extends Runtime {
            public function __construct(Kernel $kernel)
            {
                parent::__construct(static function (int $meaningOfLife): void {}, $kernel); // @phpstan-ignore-line
            }
        };
    }
}
