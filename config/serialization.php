<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Serialization\CompressingSerializer;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use Nayleen\Async\Serialization\IgbinarySerializer;

return [
    // serialization services
    CompressingSerializer::class => static function (Serializer $serializer): CompressingSerializer {
        return new CompressingSerializer($serializer);
    },

    Serializer::class => static function (): Serializer {
        // prefer igbinary over native serializer
        if (extension_loaded('igbinary')) {
            return new IgbinarySerializer();
        }

        return new NativeSerializer();
    },
];
