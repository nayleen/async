<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use DI;
use Nayleen\Async\Component\Configuration\FileLoader;
use Stringable;

/**
 * @api
 */
abstract class Component implements Stringable
{
    use ForbidCloning;
    use ForbidSerialization;

    public function __construct()
    {
    }

    /**
     * @param non-empty-string ...$filenames
     */
    final protected function load(DI\ContainerBuilder $containerBuilder, string ...$filenames): void
    {
        FileLoader::load($containerBuilder, ...$filenames);
    }

    public function boot(Kernel $kernel): void
    {
    }

    /**
     * @return non-empty-string
     */
    abstract public function name(): string;

    abstract public function register(DI\ContainerBuilder $containerBuilder): void;

    public function reload(Kernel $kernel): void
    {
    }

    public function shutdown(Kernel $kernel): void
    {
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->name();
    }
}
