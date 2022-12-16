<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Amp\Loop\DriverFactory;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Definition;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;
use Monolog\ErrorHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Dotenv\Dotenv;

use function Amp\ByteStream\getStdout;

final class Configurator
{
    private const KEY_APP = 'app.';

    private const KEY_DIRS = self::KEY_APP . 'dir.';

    public function __construct(
        private readonly ContainerBuilder $containerBuilder,
        private readonly string $name,
        private readonly string $baseDir,
        private readonly string $env,
        private readonly bool $debug,
    ) {
        $this->defaults();
    }

    public static function init(
        string $baseDir,
        string $name = 'AsyncKernel',
        ?string $env = null,
        ?bool $debug = null,
    ): self {
        $baseDir = self::baseDirectory($baseDir);

        $env ??= (getenv('APP_ENV') ?: 'prod');
        $env = strtolower($env);

        assert(self::loadEnvFiles($baseDir, $env));

        $debug ??= ((bool) getenv('APP_DEBUG')) !== false;

        return new self(new ContainerBuilder(), $name, $baseDir, $env, $debug);
    }

    private static function baseDirectory(string $baseDir): string
    {
        if (is_file($baseDir)) {
            $baseDir = dirname($baseDir);
        }

        $dir = $rootDir = $baseDir;
        while (!is_file($dir . '/composer.json')) {
            if ($dir === dirname($dir)) {
                return $rootDir;
            }

            $dir = dirname($dir);
        }

        return $dir;
    }

    private function createDefaultAsyncLogger(): LoggerInterface
    {
        $logLevel = $this->debug ? LogLevel::DEBUG : LogLevel::INFO;

        $stdoutHandler = new StreamHandler(getStdout(), $logLevel);
        $stdoutHandler->setFormatter(
            (new ConsoleFormatter(
                "[%datetime%] [%channel%] [%level_name%]: %message% %context% %extra%\n",
                'Y-m-d H:i:s.v',
                true,
                true,
            ))->includeStacktraces($this->debug),
        );

        $monolog = new Logger($this->name);
        $monolog->pushHandler($stdoutHandler);

        return $monolog;
    }

    private function createErrorHandler(LoggerInterface $logger): callable
    {
        $errorHandler = new ErrorHandler($logger);
        $errorHandler->registerErrorHandler(errorTypes: error_reporting());
        $errorHandler->registerExceptionHandler();
        $errorHandler->registerFatalHandler();

        $errorHandler = set_exception_handler(null);
        set_exception_handler($errorHandler);

        return $errorHandler;
    }

    private static function loadEnvFiles(string $baseDir, string $env): bool
    {
        if (!class_exists(Dotenv::class)) {
            return false;
        }

        $dotenv = new Dotenv();

        if (file_exists($baseDir . "/.env.{$env}")) {
            $dotenv->load($baseDir . "/.env.{$env}");
        } elseif (file_exists($baseDir . "/.env.{$env}.dist")) {
            $dotenv->load($baseDir . "/.env.{$env}.dist");
        } elseif (file_exists($baseDir . '/.env')) {
            $dotenv->load($baseDir . '/.env');
        } elseif (file_exists($baseDir . '/.env.dist')) {
            $dotenv->load($baseDir . '/.env.dist');
        }

        return true;
    }

    private function setupAsyncEnvironment(Container $container): void
    {
        $loop = (new DriverFactory())->create();

        if (
            !($loop instanceof Loop\TracingDriver)
            && $this->debug
        ) {
            $loop = new Loop\TracingDriver($loop);
        }

        if (!$container->has(LoggerInterface::class)) {
            $container->set(LoggerInterface::class, $logger = $this->createDefaultAsyncLogger());
        } else {
            $logger = $container->get(LoggerInterface::class);
        }

        $loop->setErrorHandler($this->createErrorHandler($logger));

        // set loop state and bind the driver
        $container->set(Loop::class, $loop);
        $container->set(Loop\Driver::class, $loop);

        Loop::set($loop);
    }

    public function annotations(bool $useAnnotations): self
    {
        $this->containerBuilder->useAnnotations($useAnnotations);

        return $this;
    }

    public function autowire(bool $useAutowiring): self
    {
        $this->containerBuilder->useAutowiring($useAutowiring);

        return $this;
    }

    public function cache(string $apcuNamespace): self
    {
        $this->containerBuilder->enableDefinitionCache($apcuNamespace);

        return $this;
    }

    public function compiled(): bool
    {
        return $this->containerBuilder->isCompilationEnabled();
    }

    public function create(): Kernel
    {
        $container = $this->containerBuilder->build();

        $kernel = new Kernel($container);
        $container->set(Kernel::class, $kernel);

        $this->setupAsyncEnvironment($container);

        return $kernel;
    }

    public function defaults(): self
    {
        return $this
            ->annotations(false)
            ->autowire(true)
            ->lax()
            ->parameters(
                [
                    // app config
                    self::KEY_APP . 'debug' => $this->debug,
                    self::KEY_APP . 'env' => $this->env,
                    self::KEY_APP . 'name' => $this->name,

                    // initial directories config
                    self::KEY_DIRS . 'base' => $this->baseDir,
                ],
            );
    }

    public function define(string|array|DefinitionSource ...$definitions): self
    {
        $this->containerBuilder->addDefinitions(...$definitions);

        return $this;
    }

    public function dir(string $name, string $dir, string $relativeTo = 'base'): self
    {
        if (!str_starts_with($name, self::KEY_DIRS)) {
            $name = self::KEY_DIRS . trim($name, '.');
        }

        $this->parameter(
            $name,
            new StringDefinition(sprintf('{%s%s}/%s', self::KEY_DIRS, $relativeTo, ltrim($dir, '/'))),
        );

        return $this;
    }

    public function lax(): self
    {
        $this->containerBuilder->ignorePhpDocErrors(true);

        return $this;
    }

    public function load(string $configFile): self
    {
        if (
            !file_exists($configFile)
            && !file_exists($configFile = $this->baseDir . '/' . ltrim($configFile, '/'))
        ) {
            throw new InvalidArgumentException();
        }

        /** @var callable(ContainerBuilder): void $configurator */
        $configurator = require $configFile;
        assert(is_callable($configurator));

        $configurator($this->containerBuilder);

        return $this;
    }

    public function parameter(string $name, mixed $value): self
    {
        $this->parameters([$name => $value]);

        return $this;
    }

    public function parameters(array $parameters): self
    {
        $mapped = [];
        foreach ($parameters as $name => $value) {
            $mapped[$name] = $value instanceof Definition
                ? $value
                : new ValueDefinition($value);
        }

        $this->define($mapped);

        return $this;
    }

    public function strict(): self
    {
        $this->containerBuilder->ignorePhpDocErrors(false);

        return $this;
    }

    public function wrap(ContainerInterface $container): self
    {
        $this->containerBuilder->wrapContainer($container);

        return $this;
    }
}
