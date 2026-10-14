<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Catalog;

use Lada\Core\Domain\Exception\InvalidCatalog;

/**
 * Product search as requested by an agent. Ranking is entirely up to the shop's search engine.
 */
final readonly class SearchQuery
{
    public const int DEFAULT_LIMIT = 20;
    public const int MAX_LIMIT = 100;

    /**
     * @param string|null $text   free-text query; null means "browse"
     * @param string|null $cursor opaque cursor from ProductPage::$nextCursor
     */
    public function __construct(
        public PricingContext $context,
        public ?string $text = null,
        public int $limit = self::DEFAULT_LIMIT,
        public ?string $cursor = null,
    ) {
        if (null !== $text && '' === trim($text)) {
            throw InvalidCatalog::emptyText('Search text');
        }

        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw InvalidCatalog::limitOutOfRange($limit, self::MAX_LIMIT);
        }

        if ('' === $cursor) {
            throw InvalidCatalog::emptyText('Search cursor');
        }
    }
}
