<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component\Configuration;

use DI\ContainerBuilder;
use InvalidArgumentException;
use Safe;

/**
 * @psalm-internal Nayleen\Async
 */
final class FileLoader
{
    /**
     * @param non-empty-string ...$filenames
     */
    public static function load(ContainerBuilder $containerBuilder, string ...$filenames): void
    {
        foreach ($filenames as $filename) {
            foreach (Safe\glob($filename) as $file) {
                $definitions = (static function () use ($file): array {
                    assert(
                        file_exists($file) && is_file($file),
                        new InvalidArgumentException(sprintf('Config file "%s" does not exist!', $file)),
                    );

                    $config = require $file;
                    assert(is_array($config));

                    return $config;
                })();

                $containerBuilder->addDefinitions($definitions);
            }
        }
    }
}
