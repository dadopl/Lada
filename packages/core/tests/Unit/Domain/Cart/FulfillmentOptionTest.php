<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\FulfillmentOption;
use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FulfillmentOptionTest extends TestCase
{
    #[Test]
    public function it_describes_a_shipping_option(): void
    {
        // Arrange
        $price = Money::of(1599, Currency::of('PLN'));

        // Act
        $option = new FulfillmentOption('inpost', 'InPost Paczkomat', $price, '1-2 days');

        // Assert
        self::assertSame('inpost', $option->id);
        self::assertSame('1-2 days', $option->deliveryEstimate);
    }

    #[Test]
    #[DataProvider('emptyFields')]
    public function it_rejects_an_empty_id_or_title(string $id, string $title): void
    {
        // Arrange
        $price = Money::of(1599, Currency::of('PLN'));

        // Assert
        $this->expectException(InvalidCart::class);

        // Act
        new FulfillmentOption($id, $title, $price);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function emptyFields(): iterable
    {
        yield 'empty id' => ['', 'Courier'];
        yield 'empty title' => ['courier', ''];
    }
}
