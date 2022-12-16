<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;

final class Container implements ContainerInterface
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
     * @var array<class-string, object|null>
     */
    private array $trackedResolves = [];

    private const FACTORY_SUFFIX = '.factory';

    public function __construct(Kernel $kernel)
    {
        $this->runtimeDependencies = [
            self::class => $this,
            ContainerInterface::class => $this,
            Kernel::class => $kernel,
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

            if ($type === null) {
                if (!isset($userParameters[$name])) {
                    throw new Exception();
                }

                $parameters[$name] = $userParameters[$name];
                continue;
            }

            $parameters[$name] = match (true) {
                $type instanceof ReflectionNamedType => $this->resolve($type, $userParameters),
                $type instanceof ReflectionIntersectionType,
                $type instanceof ReflectionUnionType => $this->resolveAll($type, $userParameters),
            };
        }

        $this->trackedResolves = [];

        return array_replace($parameters, $userParameters);
    }

    /**
     * @param class-string $id
     */
    private function create(string $id, array $parameters): ?object
    {
        $factoryId = $this->factoryId($id);

        // use registered factory to create the service
        if (isset($this->runtimeDependencies[$factoryId])) {
            $factory = $this->runtimeDependencies[$factoryId];
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

        $parameters = $this->autowire($constructor->getParameters(), $parameters);

        return $reflection->newInstanceArgs($parameters);
    }

    private function factoryId(string $id): string
    {
        return sprintf('%s%s', $id, self::FACTORY_SUFFIX);
    }

    private function resolve(ReflectionNamedType $type, array $userParameters): ?object
    {
        $typeName = $type->getName();

        if (array_key_exists($typeName, $this->trackedResolves)) {
            throw new Exception();
        }

        $this->trackedResolves[$typeName] = null;

        try {
            $value = $this->make($typeName, $userParameters);
        } catch (Throwable $ex) {
            if (!$type->allowsNull()) {
                throw $ex;
            }

            $value = null;
        }

        return $value;
    }

    private function resolveAll(ReflectionIntersectionType|ReflectionUnionType $type, array $userParameters): ?object
    {
        foreach ($type->getTypes() as $namedType) {
            try {
                return $this->resolve($namedType, $userParameters);
            } catch (Throwable) {
            }
        }

        throw new Exception();
    }

    public function add(ContainerInterface $container): self
    {
        $this->delegateLookupContainers[] = $container;

        return $this;
    }

    public function alias(string $alias, string $original): self
    {
        $this->factory($alias, fn () => $this->get($original));

        return $this;
    }

    public function env(string $param, string $default = null): string
    {
        $hasDefault = ($default !== null);
        $value = getenv($param);

        return match ($value) {
            false => match ($hasDefault) {
                true => $default,
                false => throw new Exception(),
            },
            default => $value,
        };
    }

    public function factory(string $id, callable $factory): self
    {
        $this->set($this->factoryId($id), $factory);

        return $this;
    }

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

        // finally, try building the service on the fly
        try {
            return $this->make($id);
        } catch (Throwable) {
            throw new Exception();
        }
    }

    public function has(string $id): bool
    {
        $factoryId = $this->factoryId($id);

        // lookup in runtime dependencies first
        if (isset($this->runtimeDependencies[$id]) || isset($this->runtimeDependencies[$factoryId])) {
            return true;
        }

        // then delegate to other containers
        foreach ($this->delegateLookupContainers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string $id
     * @return object
     */
    public function make(string $id, array $parameters = []): object
    {
        // we can only "make" classes available through autoloading
        if (!class_exists($id) && !$this->has($id)) {
            throw new Exception();
        }

        // short circuit if the service has already been created before
        if (isset($this->runtimeDependencies[$id])) {
            return $this->runtimeDependencies[$id];
        }

        $this->set($id, $service = $this->create($id, $parameters));

        return $service;
    }

    public function set(string $id, mixed $value): self
    {
        $this->runtimeDependencies[$id] = $value;

        return $this;
    }
}
