<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI\ContainerBuilder;
use InvalidArgumentException;
use Safe;
use Stringable;

/**
 * @api
 */
abstract class Component implements Stringable
{
    final public function __construct()
    {
    }

    /**
     * @param non-empty-string ...$filenames
     */
    final protected function load(ContainerBuilder $containerBuilder, string ...$filenames): void
    {
        foreach ($filenames as $filename) {
            foreach (Safe\glob($filename) as $file) {
                $definitions = (static function () use ($file): array {
                    assert(
                        file_exists($file) && is_file($file),
                        new InvalidArgumentException(sprintf(
                            '%s config file "%s" does not exist!',
                            static::class,
                            $file,
                        )),
                    );

                    return (array) require $file;
                })();

                $containerBuilder->addDefinitions($definitions);
            }
        }
    }

    public function boot(Kernel $kernel): void
    {
    }

    /**
     * @return non-empty-string
     */
    abstract public function name(): string;

    abstract public function register(ContainerBuilder $containerBuilder): void;

    public function reload(Kernel $kernel): void
    {
    }

    public function shutdown(Kernel $kernel): void
    {
    }

    /**
     * @return non-empty-string
     */
    final public function __toString(): string
    {
        return $this->name();
    }
}
