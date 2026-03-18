<?php

declare(strict_types = 1);

namespace DI;

use Closure;
use DI\Definition\ArrayDefinition;
use DI\Definition\Definition;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\Reference;
use olvlvl\ComposerAttributeCollector\Attributes;

/**
 * Returns a DefinitionHelper that ensures the entry exists in the container.
 */
function exists(): DefinitionHelper
{
    return decorate(static function (mixed $entry) {
        if ($entry === null) {
            throw new DependencyException('Entry does not exist in the container.');
        }

        return $entry;
    });
}

/**
 * Collects all classes tagged with the given attribute into a DI definition.
 *
 * @template T of object
 *
 * @param class-string<T> $attribute
 * @param Closure(T $attribute): bool|null $predicate
 */
function collect(string $attribute, ?Closure $predicate = null): Definition
{
    assert(class_exists($attribute));

    $definitions = [];

    foreach (Attributes::findTargetClasses($attribute) as $target) {
        if ($predicate !== null && !$predicate($target->attribute)) {
            continue;
        }

        $definitions[] = new Reference($target->name);
    }

    return new ArrayDefinition($definitions);
}
