<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\CartLine;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CartLineTest extends TestCase
{
    #[Test]
    public function it_keeps_the_prices_reported_by_the_shop(): void
    {
        // Arrange
        $unitPrice = self::pln(1000);
        $discountedTotal = self::pln(2500);

        // Act
        $line = new CartLine(new LineRef('1'), new ProductRef('SKU-1'), 'Mug', 3, $unitPrice, $discountedTotal);

        // Assert
        self::assertSame(3, $line->quantity);
        self::assertTrue($line->total->equals(self::pln(2500)));
    }

    #[Test]
    #[DataProvider('nonPositiveQuantities')]
    public function it_rejects_a_non_positive_quantity(int $quantity): void
    {
        // Arrange
        $price = self::pln(1000);

        // Assert
        $this->expectException(InvalidCart::class);

        // Act
        new CartLine(new LineRef('1'), new ProductRef('SKU-1'), 'Mug', $quantity, $price, $price);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function nonPositiveQuantities(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    #[Test]
    public function it_rejects_an_empty_title(): void
    {
        // Arrange
        $price = self::pln(1000);

        // Assert
        $this->expectException(InvalidCart::class);

        // Act
        new CartLine(new LineRef('1'), new ProductRef('SKU-1'), '', 1, $price, $price);
    }

    #[Test]
    public function it_rejects_a_total_in_another_currency_than_the_unit_price(): void
    {
        // Arrange
        $unitPrice = self::pln(1000);
        $total = Money::of(1000, Currency::of('EUR'));

        // Assert
        $this->expectException(CurrencyMismatch::class);

        // Act
        new CartLine(new LineRef('1'), new ProductRef('SKU-1'), 'Mug', 1, $unitPrice, $total);
    }

    private static function pln(int $amount): Money
    {
        return Money::of($amount, Currency::of('PLN'));
    }
}
