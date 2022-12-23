<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container;

use Closure;
use Nayleen\Async\Kernel\Container\Exception\AutowiringException;
use Nayleen\Async\Kernel\Container\Exception\CircularDependencyException;
use Nayleen\Async\Kernel\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;

final class Container implements ServiceFactory, ServiceProvider
{
    /**
     * @var ContainerInterface[]
     */
    private array $delegateLookupContainers = [];

    /**
     * @var array<class-string, mixed>
     */
    private array $runtimeDependencies;

    /**
     * @var array<class-string, bool>
     */
    private array $trackedResolves = [];

    private const FACTORY_SUFFIX = '.factory';

    public function __construct()
    {
        $this->runtimeDependencies = [
            self::class => $this,
            ContainerInterface::class => $this,
            ServiceFactory::class => $this,
            ServiceProvider::class => $this,
        ];
    }

    /**
     * @param ReflectionParameter[] $reflectionParameters
     * @param array $userParameters
     * @return array
     */
    private function autowire(array $reflectionParameters, array $userParameters): array
    {
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $name = $reflectionParameter->getName();
            $type = $reflectionParameter->getType();

            if (isset($userParameters[$name])) {
                $parameters[$name] = $userParameters[$name];
                continue;
            }

            try {
                $parameters[$name] = match (true) {
                    $type === null => match ($reflectionParameter->isDefaultValueAvailable()) {
                        true => $reflectionParameter->getDefaultValue(),
                        default => null,
                    },
                    $type instanceof ReflectionNamedType => $this->resolve($type, $userParameters),
                    $type instanceof ReflectionIntersectionType,
                    $type instanceof ReflectionUnionType => $this->resolveAll($type, $userParameters),
                };
            } catch (Throwable $ex) {
                if (!$reflectionParameter->isOptional()) {
                    throw $ex;
                }

                $parameters[$name] = $reflectionParameter->getDefaultValue();
            }
        }

        $this->trackedResolves = [];

        return array_replace($parameters, $userParameters);
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     * @return T
     */
    private function create(string $id, array $parameters): object
    {
        // use registered factory to create the service
        if (isset($this->runtimeDependencies[$this->factoryId($id)])) {
            $factory = $this->runtimeDependencies[$this->factoryId($id)];
            assert(is_callable($factory));

            $reflection = new ReflectionFunction($factory);
            $parameters = $this->autowire($reflection->getParameters(), $parameters);

            return $reflection->invokeArgs($parameters);
        }

        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();

        if (!$constructor && $reflection->isInstantiable()) {
            return $reflection->newInstance();
        }

        if (!$constructor->isPublic()) {
            throw AutowiringException::privateConstructor($reflection);
        }

        if (isset($this->trackedResolves[$id])) {
            throw new CircularDependencyException($id, array_key_last($this->trackedResolves));
        }

        $this->trackedResolves[$id] = true;

        $parameters = $this->autowire($constructor->getParameters(), $parameters);

        return $reflection->newInstanceArgs($parameters);
    }

    private function factoryId(string $id): string
    {
        return sprintf('%s%s', $id, self::FACTORY_SUFFIX);
    }

    private function resolve(ReflectionNamedType $type, array $userParameters): object
    {
        $typeName = $type->getName();
        $this->trackedResolves[$typeName] = true;

        return $this->make($typeName, $userParameters);
    }

    private function resolveAll(ReflectionIntersectionType|ReflectionUnionType $type, array $userParameters): object
    {
        foreach ($type->getTypes() as $namedType) {
            try {
                return $this->resolve($namedType, $userParameters);
            } catch (Throwable) {
            }
        }

        throw AutowiringException::cannotResolveCombinedType(array_key_last($this->trackedResolves), $type);
    }

    /**
     * @internal
     */
    public function add(ContainerInterface $container): self
    {
        $this->delegateLookupContainers[] = $container;

        return $this;
    }

    /**
     * @api
     *
     * @param non-empty-string $alias
     * @param non-empty-string $originalId
     */
    public function alias(string $alias, string $originalId): self
    {
        // aliases are basically lazy factories referencing original
        $this->factory($alias, fn () => $this->get($originalId));

        return $this;
    }

    /**
     * @api
     *
     * @param non-empty-string $param
     */
    public function env(string $param, string $default = null): string
    {
        $value = getenv($param);

        return match ($value) {
            false => match ($default !== null) {
                true => $default,
                false => throw NotFoundException::missingEnvironmentParameter($param),
            },
            default => $value,
        };
    }

    /**
     * @api
     *
     * @param non-empty-string $id
     */
    public function factory(string $id, Closure $factory): self
    {
        $this->set($this->factoryId($id), $factory);

        return $this;
    }

    /**
     * @api
     *
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     * @return mixed|T
     */
    public function get(string $id): mixed
    {
        // lookup in runtime dependencies first
        if (isset($this->runtimeDependencies[$id])) {
            return $this->runtimeDependencies[$id];
        }

        // then delegate to other containers
        foreach ($this->delegateLookupContainers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        // finally try creating service on the fly
        return $this->make($id);
    }

    /**
     * @api
     *
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     */
    public function has(string $id): bool
    {
        $factoryId = $this->factoryId($id);

        // try lookup in runtime dependencies
        if (isset($this->runtimeDependencies[$id]) || isset($this->runtimeDependencies[$factoryId])) {
            return true;
        }

        // delegate to other containers
        foreach ($this->delegateLookupContainers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        // not available
        return false;
    }

    /**
     * @api
     *
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     * @return T
     */
    public function make(string $id, array $parameters = []): object
    {
        // can only "make" classes available through autoloading
        // or having been previously set
        if (!class_exists($id) && !$this->has($id)) {
            throw NotFoundException::notAutoloadable($id);
        }

        // short circuit if service has already been created before
        if (isset($this->runtimeDependencies[$id])) {
            return $this->runtimeDependencies[$id];
        }

        $instance = $this->create($id, $parameters);

        if (count($parameters) === 0) {
            $this->set($id, $instance);
        }

        return $instance;
    }

    /**
     * @api
     *
     * @param non-empty-string $id
     */
    public function set(string $id, mixed $value): self
    {
        $this->runtimeDependencies[$id] = $value;

        return $this;
    }
}
