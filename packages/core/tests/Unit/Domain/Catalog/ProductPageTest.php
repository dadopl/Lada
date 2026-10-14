<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Catalog;

use Lada\Core\Domain\Catalog\ProductPage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProductPageTest extends TestCase
{
    #[Test]
    public function it_has_a_next_page_when_the_shop_returns_a_cursor(): void
    {
        // Arrange
        $page = new ProductPage([], 'page-2');

        // Act
        $hasNextPage = $page->hasNextPage();

        // Assert
        self::assertTrue($hasNextPage);
    }

    #[Test]
    public function it_is_the_last_page_without_a_cursor(): void
    {
        // Arrange
        $page = new ProductPage([]);

        // Act
        $hasNextPage = $page->hasNextPage();

        // Assert
        self::assertFalse($hasNextPage);
    }
}
