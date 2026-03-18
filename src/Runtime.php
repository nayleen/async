<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI\Container;
use Override;
use ReflectionClass;
use ReflectionFunction;
use Symfony\Component\Runtime\GenericRuntime;
use Symfony\Component\Runtime\Resolver\ClosureResolver;
use Symfony\Component\Runtime\ResolverInterface;

use function Safe\ini_get;

/**
 * A runtime for running asynchronous applications.
 */
class Runtime extends GenericRuntime
{
    /**
     * @psalm-internal Nayleen\Async
     */
    public readonly Container $container;

    /**
     * @param array{
     *    config_dir?: ?string,
     *    debug?: bool,
     *    debug_var_name?: ?string,
     *    env?: ?string,
     *    env_var_name?: ?string,
     *    project_dir?: string,
     *    tmp_dir?: ?string,
     * } $options
     */
    public function __construct(array $options = [])
    {
        $debugKey = $options['debug_var_name'] ??= 'APP_DEBUG';
        $debug = $options['debug'] ?? $_ENV[$debugKey] ?? $_SERVER[$debugKey] ?? ini_get('zend.assertions');

        if (!is_bool($debug)) {
            $debug = filter_var($debug, FILTER_VALIDATE_BOOL);
        }

        $options['debug'] = $debug;

        $envKey = $options['env_var_name'] ??= 'APP_ENV';
        $env = $options['env'] ?? $_ENV[$envKey] ?? $_SERVER[$envKey] ?? 'prod';
        assert(is_string($env));

        parent::__construct($options);

        $projectDir = $this->options['project_dir'];
        assert(is_string($projectDir));

        $this->container = Bootstrapper::run(
            projectRoot: $projectDir,
            env: $env,
            debug: $debug,
            configDir: $options['config_dir'] ?? Bootstrapper::DEFAULT_CONFIG_DIR,
            tmpDir: $options['tmp_dir'] ?? Bootstrapper::DEFAULT_TMP_DIR,
        );
    }

    /**
     * @param callable(mixed ...$args): void $callable
     */
    #[Override]
    public function getResolver(callable $callable, ?ReflectionFunction $reflector = null): ResolverInterface
    {
        return new ClosureResolver(
            fn () => new Runner($this->container, $callable(...)),
            static fn () => [],
        );
    }
}
