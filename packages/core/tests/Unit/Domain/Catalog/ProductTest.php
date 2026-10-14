<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Catalog;

use Lada\Core\Domain\Catalog\Availability;
use Lada\Core\Domain\Catalog\Product;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCatalog;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use Lada\Core\Domain\Shared\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    #[Test]
    #[DataProvider('availabilities')]
    public function it_is_purchasable_unless_out_of_stock(Availability $availability, bool $expected): void
    {
        // Arrange
        $product = self::product($availability);

        // Act
        $purchasable = $product->isPurchasable();

        // Assert
        self::assertSame($expected, $purchasable);
    }

    /**
     * @return iterable<string, array{Availability, bool}>
     */
    public static function availabilities(): iterable
    {
        yield 'in stock' => [Availability::InStock, true];
        yield 'backorder' => [Availability::BackOrder, true];
        yield 'preorder' => [Availability::PreOrder, true];
        yield 'out of stock' => [Availability::OutOfStock, false];
    }

    #[Test]
    public function it_keeps_the_list_price_of_a_promotion(): void
    {
        // Arrange
        $listPrice = Money::of(4900, Currency::of('PLN'));

        // Act
        $product = new Product(
            ref: new ProductRef('MUG-RED'),
            title: 'Red mug',
            price: Money::of(3900, Currency::of('PLN')),
            availability: Availability::InStock,
            url: Url::of('https://shop.example/red-mug'),
            listPrice: $listPrice,
        );

        // Assert
        self::assertSame($listPrice, $product->listPrice);
    }

    #[Test]
    public function it_rejects_a_blank_title(): void
    {
        // Arrange
        $price = Money::of(3900, Currency::of('PLN'));

        // Assert
        $this->expectException(InvalidCatalog::class);

        // Act
        new Product(new ProductRef('MUG-RED'), ' ', $price, Availability::InStock, Url::of('https://shop.example/red-mug'));
    }

    #[Test]
    public function it_rejects_a_list_price_in_another_currency(): void
    {
        // Arrange
        $price = Money::of(3900, Currency::of('PLN'));
        $listPrice = Money::of(4900, Currency::of('EUR'));

        // Assert
        $this->expectException(CurrencyMismatch::class);

        // Act
        new Product(
            ref: new ProductRef('MUG-RED'),
            title: 'Red mug',
            price: $price,
            availability: Availability::InStock,
            url: Url::of('https://shop.example/red-mug'),
            listPrice: $listPrice,
        );
    }

    private static function product(Availability $availability): Product
    {
        return new Product(
            new ProductRef('MUG-RED'),
            'Red mug',
            Money::of(3900, Currency::of('PLN')),
            $availability,
            Url::of('https://shop.example/red-mug'),
        );
    }
}
