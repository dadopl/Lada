<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Catalog;

/**
 * One page of search results, in the order ranked by the shop.
 */
final readonly class ProductPage
{
    /**
     * @param list<Product> $products
     * @param string|null   $nextCursor opaque cursor for the next page; null on the last page
     */
    public function __construct(
        public array $products,
        public ?string $nextCursor = null,
    ) {}

    public function hasNextPage(): bool
    {
        return null !== $this->nextCursor;
    }
}
