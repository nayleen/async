<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Closure;
use DI\Container;
use Symfony\Component\Runtime\RunnerInterface;
use Throwable;

/**
 * @psalm-internal Nayleen\Async
 */
final readonly class Runner implements RunnerInterface
{
    /**
     * @param Closure(mixed ...$args): void $entrypoint
     */
    public function __construct(
        private Container $container,
        private Closure $entrypoint,
    ) {}

    public function run(): int
    {
        $kernel = $this->container->get(Kernel::class);

        try {
            $kernel->run($this->container, $this->entrypoint);
        } catch (Throwable) {
            return 1;
        }

        return 0;
    }
}
