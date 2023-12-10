<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Serialization\CompressingSerializer;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;

return [
    // serialization services
    CompressingSerializer::class => static fn (
        Serializer $serializer,
    ): CompressingSerializer => new CompressingSerializer($serializer),

    Serializer::class => static function (): Serializer {
        // Prefer igbinary over msgpack over native PHP serialization
        // if the respective extensions are loaded
        if (extension_loaded('igbinary')) {
            return new Serialization\IgbinarySerializer();
        }

        if (extension_loaded('msgpack')) {
            return new Serialization\MessagePackSerializer();
        }

        return new NativeSerializer();
    },
];
