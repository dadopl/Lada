<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Catalog;

use Lada\Core\Domain\Catalog\PricingContext;
use Lada\Core\Domain\Catalog\SearchQuery;
use Lada\Core\Domain\Exception\InvalidCatalog;
use Lada\Core\Domain\Shared\Channel;
use Lada\Core\Domain\Shared\Country;
use Lada\Core\Domain\Shared\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SearchQueryTest extends TestCase
{
    #[Test]
    public function it_browses_the_first_page_by_default(): void
    {
        // Arrange
        $context = self::context();

        // Act
        $query = new SearchQuery($context);

        // Assert
        self::assertNull($query->text);
        self::assertNull($query->cursor);
        self::assertSame(SearchQuery::DEFAULT_LIMIT, $query->limit);
    }

    #[Test]
    public function it_carries_text_limit_and_cursor(): void
    {
        // Arrange
        $context = self::context();

        // Act
        $query = new SearchQuery($context, 'red mug', 100, 'page-2');

        // Assert
        self::assertSame('red mug', $query->text);
        self::assertSame(100, $query->limit);
        self::assertSame('page-2', $query->cursor);
    }

    #[Test]
    #[DataProvider('invalidQueries')]
    public function it_rejects_an_invalid_query(?string $text, int $limit, ?string $cursor): void
    {
        // Arrange
        $context = self::context();

        // Assert
        $this->expectException(InvalidCatalog::class);

        // Act
        new SearchQuery($context, $text, $limit, $cursor);
    }

    /**
     * @return iterable<string, array{?string, int, ?string}>
     */
    public static function invalidQueries(): iterable
    {
        yield 'blank text' => ['  ', 20, null];
        yield 'zero limit' => ['mug', 0, null];
        yield 'limit above max' => ['mug', SearchQuery::MAX_LIMIT + 1, null];
        yield 'empty cursor' => ['mug', 20, ''];
    }

    private static function context(): PricingContext
    {
        return PricingContext::forChannel(new Channel('default', Currency::of('PLN'), 'pl_PL', Country::of('PL')));
    }
}
