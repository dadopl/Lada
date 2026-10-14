<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TotalsTest extends TestCase
{
    #[Test]
    public function it_keeps_the_amounts_from_the_pricing_engine(): void
    {
        // Arrange
        $pln = Currency::of('PLN');

        // Act
        $totals = new Totals(
            subtotal: Money::of(10000, $pln),
            discount: Money::of(1000, $pln),
            shipping: Money::of(1599, $pln),
            tax: Money::of(1983, $pln),
            total: Money::of(10599, $pln),
        );

        // Assert
        self::assertTrue($totals->currency->equals($pln));
        self::assertSame(10599, $totals->total->amount);
    }

    #[Test]
    #[DataProvider('foreignAmountPositions')]
    public function it_rejects_amounts_in_different_currencies(int $position): void
    {
        // Arrange
        $amounts = array_fill(0, 5, Money::of(100, Currency::of('PLN')));
        $amounts[$position] = Money::of(100, Currency::of('EUR'));

        // Assert
        $this->expectException(CurrencyMismatch::class);

        // Act
        new Totals(...$amounts);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function foreignAmountPositions(): iterable
    {
        yield 'subtotal' => [0];
        yield 'discount' => [1];
        yield 'shipping' => [2];
        yield 'tax' => [3];
        yield 'total' => [4];
    }
}
