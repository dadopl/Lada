<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Checkout;

use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Exception\InvalidIdentifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckoutSessionIdTest extends TestCase
{
    #[Test]
    public function it_generates_a_uuid_v4(): void
    {
        // Arrange
        $uuidV4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

        // Act
        $id = CheckoutSessionId::generate();

        // Assert
        self::assertMatchesRegularExpression($uuidV4, $id->value);
    }

    #[Test]
    public function it_generates_unique_ids(): void
    {
        // Arrange
        $count = 100;

        // Act
        $ids = array_map(static fn(): string => CheckoutSessionId::generate()->value, range(1, $count));

        // Assert
        self::assertCount($count, array_unique($ids));
    }

    #[Test]
    public function it_is_restored_from_a_string_in_lowercase(): void
    {
        // Arrange
        $value = '8E03978E-40D5-43E8-BC93-6894A57F9324';

        // Act
        $id = CheckoutSessionId::fromString($value);

        // Assert
        self::assertSame('8e03978e-40d5-43e8-bc93-6894a57f9324', $id->value);
        self::assertSame('8e03978e-40d5-43e8-bc93-6894a57f9324', (string) $id);
    }

    #[Test]
    #[DataProvider('invalidIds')]
    public function it_rejects_a_value_that_is_not_a_uuid(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Assert
        $this->expectException(InvalidIdentifier::class);

        // Act
        CheckoutSessionId::fromString($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidIds(): iterable
    {
        yield 'empty' => [''];
        yield 'no dashes' => ['8e03978e40d543e8bc936894a57f9324'];
        yield 'not hex' => ['8e03978e-40d5-43e8-bc93-6894a57f932z'];
        yield 'trailing newline' => ["8e03978e-40d5-43e8-bc93-6894a57f9324\n"];
    }

    #[Test]
    public function it_compares_by_value(): void
    {
        // Arrange
        $a = CheckoutSessionId::fromString('8e03978e-40d5-43e8-bc93-6894a57f9324');
        $b = CheckoutSessionId::fromString('8E03978E-40D5-43E8-BC93-6894A57F9324');
        $c = CheckoutSessionId::generate();

        // Act
        $sameEquals = $a->equals($b);
        $differentEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameEquals);
        self::assertFalse($differentEquals);
    }
}
