<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\InvalidCart;

/**
 * A line the agent wants in the cart: what and how many. Prices are always the shop's.
 */
final readonly class DraftLine
{
    public function __construct(
        public ProductRef $product,
        public int $quantity,
    ) {
        if ($quantity <= 0) {
            throw InvalidCart::nonPositiveQuantity($quantity);
        }
    }
}
