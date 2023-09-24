<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use OutOfBoundsException;

/**
 * @internal
 */
final class EnvironmentTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function returns_default_on_undefined_variable(): void
    {
        self::assertTrue(Environment::get('THIS_ENV_VAR_DOES_NOT_EXIST', true));
    }

    /**
     * @test
     */
    public function throws_on_undefined_variable(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Environment::get('THIS_ENV_VAR_DOES_NOT_EXIST');
    }
}
