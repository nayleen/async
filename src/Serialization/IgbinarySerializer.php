<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use Throwable;

readonly class IgbinarySerializer implements Serializer
{
    public function __construct()
    {
        assert(
            extension_loaded('igbinary'),
            new SerializationException('The igbinary extension must be loaded to use this serializer'),
        );
    }

    public function serialize(mixed $data): string
    {
        try {
            $serialized = igbinary_serialize($data);
            assert(is_string($serialized));

            return $serialized;
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to serialize data', 0, $throwable);
        }
    }

    public function unserialize(string $data): mixed
    {
        try {
            $unserialized = igbinary_unserialize($data);
            assert($unserialized !== false);

            return $unserialized;
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to unserialize data', 0, $throwable);
        }
    }
}
