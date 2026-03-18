<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use Throwable;

use function Amp\Serialization\encodeUnprintableChars;

final class IgbinarySerializer implements Serializer
{
    public function serialize($data): string
    {
        $serialized = igbinary_serialize($data);

        if ($serialized === null) {
            throw new SerializationException('The given data could not be serialized: igbinary_serialize returned null');
        }

        return $serialized;
    }

    public function unserialize(string $data): mixed
    {
        try {
            $result = igbinary_unserialize($data);

            if ($result === false && $data !== igbinary_serialize(false)) {
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
