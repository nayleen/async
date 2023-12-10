<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use Throwable;

final class MessagePackSerializer implements Serializer
{
    public function __construct()
    {
        assert(
            extension_loaded('msgpack'),
            new SerializationException('The msgpack extension must be loaded to use this serializer'),
        );
    }

    public function serialize(mixed $data): string
    {
        try {
            return msgpack_serialize($data);
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to serialize data', 0, $throwable);
        }
    }

    public function unserialize(string $data): mixed
    {
        try {
            return msgpack_unserialize($data);
        } catch (Throwable $throwable) {
            throw new SerializationException('Failed to unserialize data', 0, $throwable);
        }
    }
}
