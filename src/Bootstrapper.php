<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI;
use Safe;

/**
 * @psalm-internal Nayleen\Async
 */
final readonly class Bootstrapper
{
    /**
     * @var non-empty-string
     */
    public const string DEFAULT_CONFIG_DIR = 'config';

    /**
     * @var non-empty-string
     */
    public const string DEFAULT_TMP_DIR = 'var/tmp';

    public static function run(
        string $projectRoot,
        string $env = 'prod',
        bool $debug = false,
        string $configDir = self::DEFAULT_CONFIG_DIR,
        string $tmpDir = self::DEFAULT_TMP_DIR,
    ): DI\Container {
        $containerBuilder = (new DI\ContainerBuilder())
            ->useAttributes(false)
            ->useAutowiring(true);

        if (str_starts_with($tmpDir, '/')) {
            $tmpDir = rtrim($tmpDir, '/');
        } else {
            $tmpDir = "{$projectRoot}/" . rtrim($tmpDir, '/');
        }

        if (!is_dir($tmpDir)) {
            Safe\mkdir($tmpDir, 0775, true);
        }

        assert(file_exists($tmpDir) && is_dir($tmpDir) && is_writable($tmpDir));

        // load core configs
        $containerBuilder->addDefinitions([
            'app.debug' => DI\value($debug),
            'app.dir.root' => DI\value($projectRoot),
            'app.dir.tmp' => DI\value($tmpDir),
            'app.env' => DI\value($env),
        ]);

        self::load($containerBuilder, __DIR__ . '/Resources/config');
        self::load($containerBuilder, __DIR__ . "/Resources/config/{$env}");

        // load app configs
        $configPath = "{$projectRoot}/{$configDir}";

        self::load($containerBuilder, $configPath);
        self::load($containerBuilder, "{$configPath}/{$env}");

        // enable container compilation
        if ($env === 'prod' && !$debug) {
            $containerBuilder->enableCompilation("{$tmpDir}/container");
        }

        $container = $containerBuilder->build();
        unset($containerBuilder);

        return $container;
    }

    /**
     * @param DI\ContainerBuilder<DI\Container> $containerBuilder
     * @param non-empty-string $configDir
     */
    private static function load(DI\ContainerBuilder $containerBuilder, string $configDir): void
    {
        if (!is_dir($configDir) || !is_readable($configDir)) {
            return;
        }

        foreach (Safe\glob($configDir . '/*.php') as $configFile) {
            $definitions = require $configFile;
            assert(is_array($definitions));

            $containerBuilder->addDefinitions($definitions);
        }
    }
}
