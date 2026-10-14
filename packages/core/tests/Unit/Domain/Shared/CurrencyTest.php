<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidCurrency;
use Lada\Core\Domain\Shared\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    #[Test]
    public function it_is_created_from_an_iso_code(): void
    {
        // Arrange
        $code = 'PLN';

        // Act
        $currency = Currency::of($code);

        // Assert
        self::assertSame('PLN', $currency->code);
        self::assertSame('PLN', (string) $currency);
    }

    #[Test]
    #[DataProvider('invalidCodes')]
    public function it_rejects_a_code_that_is_not_iso_4217(string $code): void
    {
        // Arrange
        // (invalid code from the data provider)

        // Assert
        $this->expectException(InvalidCurrency::class);

        // Act
        Currency::of($code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'empty' => [''];
        yield 'lowercase' => ['pln'];
        yield 'too short' => ['PL'];
        yield 'too long' => ['PLNX'];
        yield 'digits' => ['985'];
    }

    #[Test]
    public function it_equals_a_currency_with_the_same_code(): void
    {
        // Arrange
        $a = Currency::of('EUR');
        $b = Currency::of('EUR');

        // Act
        $equals = $a->equals($b);

        // Assert
        self::assertTrue($equals);
    }

    #[Test]
    public function it_does_not_equal_a_currency_with_a_different_code(): void
    {
        // Arrange
        $a = Currency::of('EUR');
        $b = Currency::of('USD');

        // Act
        $equals = $a->equals($b);

        // Assert
        self::assertFalse($equals);
    }
}
