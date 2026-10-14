<?php

declare(strict_types=1);

namespace Lada\Core\Application\Port;

use Lada\Core\Domain\Catalog\PricingContext;
use Lada\Core\Domain\Catalog\Product;
use Lada\Core\Domain\Catalog\ProductPage;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Catalog\SearchQuery;

/**
 * Implemented by the shop: product search and lookup with final prices for the given context.
 */
interface CatalogPort
{
    /**
     * Ranks with the shop's own search engine.
     */
    public function search(SearchQuery $query): ProductPage;

    /**
     * Returns the products that exist and are visible in the context; unknown refs are skipped.
     *
     * @param list<ProductRef> $refs
     *
     * @return list<Product>
     */
    public function lookup(array $refs, PricingContext $context): array;
}
