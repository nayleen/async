<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use DI\Container;
use InvalidArgumentException;

final readonly class SerializerFactory
{
    /**
     * @var array<non-empty-string, class-string<Serializer>>
     */
    private const array SUPPORTED_TYPES = [
        'igbinary' => IgbinarySerializer::class,
        'msgpack' => MsgpackSerializer::class,
        'native' => NativeSerializer::class,
        'php' => NativeSerializer::class,
    ];

    public function __invoke(Container $container, ?string $type): Serializer
    {
        if ($type === null) {
            $type = match (true) {
                extension_loaded('igbinary') => 'igbinary',
                extension_loaded('msgpack') => 'msgpack',
                default => 'native',
            };
        }

        if (!isset(self::SUPPORTED_TYPES[$type])) {
            throw new InvalidArgumentException("Unsupported serializer type: {$type}");
        }

        $serializer = $container->make(self::SUPPORTED_TYPES[$type]);
        assert($serializer instanceof Serializer);

        return $serializer;
    }
}
