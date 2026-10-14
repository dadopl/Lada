<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\CartLine;
use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\FulfillmentOption;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CartSnapshotTest extends TestCase
{
    #[Test]
    public function it_returns_the_selected_fulfillment_option(): void
    {
        // Arrange
        $courier = new FulfillmentOption('courier', 'Courier', self::pln(1599));
        $pickup = new FulfillmentOption('pickup', 'Pickup in store', self::pln(0));
        $snapshot = new CartSnapshot(
            ref: new CartRef('42'),
            lines: [self::line('1')],
            totals: self::totals(),
            fulfillmentOptions: [$courier, $pickup],
            selectedFulfillmentOptionId: 'pickup',
        );

        // Act
        $selected = $snapshot->selectedFulfillmentOption();

        // Assert
        self::assertSame($pickup, $selected);
    }

    #[Test]
    public function it_returns_no_fulfillment_option_when_none_is_selected(): void
    {
        // Arrange
        $snapshot = new CartSnapshot(
            ref: new CartRef('42'),
            lines: [self::line('1')],
            totals: self::totals(),
            fulfillmentOptions: [new FulfillmentOption('courier', 'Courier', self::pln(1599))],
        );

        // Act
        $selected = $snapshot->selectedFulfillmentOption();

        // Assert
        self::assertNull($selected);
    }

    #[Test]
    public function it_rejects_a_selected_option_that_is_not_offered(): void
    {
        // Arrange
        $options = [new FulfillmentOption('courier', 'Courier', self::pln(1599))];

        // Assert
        $this->expectException(InvalidCart::class);
        $this->expectExceptionMessage('"pickup"');

        // Act
        new CartSnapshot(new CartRef('42'), [self::line('1')], self::totals(), $options, 'pickup');
    }

    #[Test]
    public function it_rejects_duplicate_lines(): void
    {
        // Arrange
        $lines = [self::line('1'), self::line('1')];

        // Assert
        $this->expectException(InvalidCart::class);
        $this->expectExceptionMessage('Cart line "1"');

        // Act
        new CartSnapshot(new CartRef('42'), $lines, self::totals());
    }

    #[Test]
    public function it_rejects_duplicate_fulfillment_options(): void
    {
        // Arrange
        $options = [
            new FulfillmentOption('courier', 'Courier', self::pln(1599)),
            new FulfillmentOption('courier', 'Courier express', self::pln(2599)),
        ];

        // Assert
        $this->expectException(InvalidCart::class);
        $this->expectExceptionMessage('Fulfillment option "courier"');

        // Act
        new CartSnapshot(new CartRef('42'), [self::line('1')], self::totals(), $options);
    }

    #[Test]
    public function it_rejects_a_line_in_another_currency_than_the_totals(): void
    {
        // Arrange
        $eur = Money::of(1000, Currency::of('EUR'));
        $line = new CartLine(new LineRef('1'), new ProductRef('SKU-1'), 'Mug', 1, $eur, $eur);

        // Assert
        $this->expectException(CurrencyMismatch::class);

        // Act
        new CartSnapshot(new CartRef('42'), [$line], self::totals());
    }

    #[Test]
    public function it_rejects_a_fulfillment_option_in_another_currency_than_the_totals(): void
    {
        // Arrange
        $option = new FulfillmentOption('courier', 'Courier', Money::of(1599, Currency::of('EUR')));

        // Assert
        $this->expectException(CurrencyMismatch::class);

        // Act
        new CartSnapshot(new CartRef('42'), [self::line('1')], self::totals(), [$option]);
    }

    private static function line(string $ref): CartLine
    {
        return new CartLine(new LineRef($ref), new ProductRef('SKU-' . $ref), 'Mug', 1, self::pln(1000), self::pln(1000));
    }

    private static function totals(): Totals
    {
        return new Totals(self::pln(1000), self::pln(0), self::pln(0), self::pln(187), self::pln(1000));
    }

    private static function pln(int $amount): Money
    {
        return Money::of($amount, Currency::of('PLN'));
    }
}
