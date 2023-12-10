<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use Throwable;

final class IgbinarySerializer implements Serializer
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

            if (!is_string($serialized)) {
                throw new SerializationException('Failed to serialize data');
            }

            return $serialized;
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to serialize data', 0, $throwable);
        }
    }

    public function unserialize(string $data): mixed
    {
        try {
            $unserialized = igbinary_unserialize($data);

            if ($unserialized === false) {
                throw new SerializationException('Failed to unserialize data');
            }

            return $unserialized;
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to unserialize data', 0, $throwable);
        }
    }
}
