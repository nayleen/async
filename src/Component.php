<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use DI;
use Nayleen\Async\Component\Advisory;
use Nayleen\Async\Component\Configuration\FileLoader;
use Stringable;

abstract readonly class Component implements Stringable
{
    use ForbidCloning;
    use ForbidSerialization;

    public function __construct() {}

    private function advise(Kernel $kernel, Advisory ...$advisories): void
    {
        foreach ($advisories as $advisory) {
            $advisory->advise($kernel);
        }
    }

    /**
     * @return iterable<Advisory>
     */
    protected function advisories(Kernel $kernel): iterable
    {
        return [];
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
        static $wantsRecommendations;

        $wantsRecommendations ??= $kernel->container()->get('async.run_recommendations');
        assert(is_bool($wantsRecommendations));

        if ($wantsRecommendations) {
            $this->advise($kernel, ...$this->advisories($kernel));
        }
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return static::class;
    }

    abstract public function register(DI\ContainerBuilder $containerBuilder): void;

    public function shutdown(Kernel $kernel): void {}

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->name();
    }
}
