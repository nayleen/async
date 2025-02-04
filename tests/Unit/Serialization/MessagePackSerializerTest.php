<?php

declare(strict_types = 1);

namespace Nayleen\Async\Serialization;

use Amp\Serialization\CompressingSerializer;
use Amp\Serialization\SerializationException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @small
 */
final class MessagePackSerializerTest extends TestCase
{
    /**
     * @return iterable<mixed[]>
     */
    public static function provideSerializableData(): iterable
    {
        yield ['test'];
        yield [1];
        yield [3.14];
        yield [['test', 1, 3.14]];
        yield [[str_repeat('a', 1024), str_repeat('b', 1024), str_repeat('c', 1024)]];
    }

    /**
     * @test
     * @dataProvider provideSerializableData
     * @depends can_unserialize_serialized_data
     */
    public function can_unserialize_compressed_serialized_data(mixed $data): void
    {
        $serializer = new CompressingSerializer(new MessagePackSerializer());
        $serialized = $serializer->serialize($data);

        self::assertEquals($data, $serializer->unserialize($serialized));
    }

    /**
     * @test
     * @dataProvider provideSerializableData
     */
    public function can_unserialize_serialized_data(mixed $data): void
    {
        $serializer = new MessagePackSerializer();
        $serialized = $serializer->serialize($data);

        self::assertEquals($data, $serializer->unserialize($serialized));
    }

    /**
     * @test
     */
    public function throws_on_invalid_data(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to serialize data');

        $serializer = new MessagePackSerializer();
        $serializer->serialize(new class() {});
    }

    /**
     * @test
     */
    public function throws_on_invalid_serialized_data(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to unserialize data');

        $serializer = new MessagePackSerializer();
        $serializer->unserialize(random_bytes(8));
    }
}
