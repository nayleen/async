<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Handler;

use Nayleen\Async\Bus\Message;
use ReflectionFunction;
use ReflectionNamedType;

trait Validator
{
    private function validateHandler(callable $handler): bool
    {
        // class-based handler implementing the interface always satisfies requirements
        if ($handler instanceof Handler) {
            return true;
        }

        $reflection = new ReflectionFunction($handler(...));

        // handlers need to only accept a Message as its only parameter
        $parameters = $reflection->getParameters();
        $parameterCount = count($parameters);

        if ($parameterCount === 0 || $parameterCount > 1) {
            return false;
        }

        // first parameter needs to accept a Message
        $type = $parameters[0]->getType();

        if (
            !$type instanceof ReflectionNamedType
            || !is_a($type->getName(), Message::class, true)
        ) {
            return false;
        }

        // handlers need to have voic as return type hint
        $return = $reflection->getReturnType();

        return (
            !$return instanceof ReflectionNamedType
            || !($return->getName() !== 'void')
        );
    }
}
