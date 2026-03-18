<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use Throwable;

use function Amp\Serialization\encodeUnprintableChars;

final class MsgpackSerializer implements Serializer
{
    public function serialize($data): string
    {
        try {
            return msgpack_serialize($data);
        } catch (Throwable $ex) {
            throw new SerializationException(
                sprintf('The given data could not be serialized: %s', $ex->getMessage()),
                0,
                $ex,
            );
        }
    }

    public function unserialize(string $data): mixed
    {
        try {
            $result = msgpack_unserialize($data);

            if ($result === false && $data !== msgpack_serialize(false)) {
                throw new SerializationException(
                    'Invalid data provided to unserialize: ' . encodeUnprintableChars($data),
                );
            }
        } catch (Throwable $ex) {
            throw new SerializationException('Exception thrown when unserializing data', 0, $ex);
        }

        return $result;
    }
}
