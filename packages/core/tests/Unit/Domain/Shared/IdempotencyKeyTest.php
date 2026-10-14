<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidIdentifier;
use Lada\Core\Domain\Shared\IdempotencyKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdempotencyKeyTest extends TestCase
{
    #[Test]
    #[DataProvider('validKeys')]
    public function it_accepts_a_header_safe_key(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Act
        $key = IdempotencyKey::fromString($value);

        // Assert
        self::assertSame($value, $key->value);
        self::assertSame($value, (string) $key);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validKeys(): iterable
    {
        yield 'uuid' => ['8e03978e-40d5-43e8-bc93-6894a57f9324'];
        yield 'single character' => ['a'];
        yield 'punctuation' => ['order_42:attempt-1'];
        yield '255 characters' => [str_repeat('k', 255)];
    }

    #[Test]
    #[DataProvider('invalidKeys')]
    public function it_rejects_a_key_that_is_not_header_safe(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Assert
        $this->expectException(InvalidIdentifier::class);

        // Act
        IdempotencyKey::fromString($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidKeys(): iterable
    {
        yield 'empty' => [''];
        yield 'space' => ['order 42'];
        yield 'newline' => ["key\n"];
        yield 'non-ascii' => ['zamówienie'];
        yield '256 characters' => [str_repeat('k', 256)];
    }

    #[Test]
    public function it_compares_by_value(): void
    {
        // Arrange
        $a = IdempotencyKey::fromString('key-1');
        $b = IdempotencyKey::fromString('key-1');
        $c = IdempotencyKey::fromString('key-2');

        // Act
        $sameEquals = $a->equals($b);
        $differentEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameEquals);
        self::assertFalse($differentEquals);
    }
}
