<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\SerializationException;
use Amp\Serialization\Serializer;
use RuntimeException;
use Throwable;

class IgbinarySerializer implements Serializer
{
    private static bool $isSupported;

    private function isSupported(): bool
    {
        return self::$isSupported = extension_loaded('igbinary');
    }

    public function serialize(mixed $data): string
    {
        assert(
            isset(self::$isSupported) || self::isSupported(),
            new SerializationException('ext-igbinary is not installed'),
        );

        $oldErrorHandler = set_error_handler(static function (int $code, string $message): never {
            throw new RuntimeException($message, $code);
        });

        try {
            try {
                $serialized = igbinary_serialize($data);
            } catch (Throwable $throwable) {
                throw new SerializationException('The given data could not be serialized', previous: $throwable);
            }
        } finally {
            set_error_handler($oldErrorHandler);
        }

        assert(is_string($serialized) && $serialized !== '');

        return $serialized;
    }

    public function unserialize(string $data): mixed
    {
        assert(
            isset(self::$isSupported) || self::isSupported(),
            new SerializationException('ext-igbinary is not installed'),
        );

        $oldErrorHandler = set_error_handler(static function (int $code, string $message): never {
            throw new RuntimeException($message, $code);
        });

        try {
            return igbinary_unserialize($data);
        } catch (RuntimeException $previous) {
            throw new SerializationException(
                sprintf('Exception thrown when unserializing data: %s', $previous->getMessage()),
                $previous->getCode(),
                $previous,
            );
        } finally {
            set_error_handler($oldErrorHandler);
        }
    }
}
