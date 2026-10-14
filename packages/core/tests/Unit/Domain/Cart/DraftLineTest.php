<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\DraftLine;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\InvalidCart;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DraftLineTest extends TestCase
{
    #[Test]
    public function it_asks_for_a_quantity_of_a_product(): void
    {
        // Arrange
        $product = new ProductRef('MUG-RED');

        // Act
        $line = new DraftLine($product, 2);

        // Assert
        self::assertSame($product, $line->product);
        self::assertSame(2, $line->quantity);
    }

    #[Test]
    #[DataProvider('nonPositiveQuantities')]
    public function it_rejects_a_non_positive_quantity(int $quantity): void
    {
        // Arrange
        $product = new ProductRef('MUG-RED');

        // Assert
        $this->expectException(InvalidCart::class);

        // Act
        new DraftLine($product, $quantity);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function nonPositiveQuantities(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-3];
    }
}
