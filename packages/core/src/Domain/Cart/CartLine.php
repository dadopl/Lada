<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Money;

/**
 * One line of the cart as priced by the shop. The line total comes from the shop
 * (it may include line discounts), Lada does not recompute it.
 */
final readonly class CartLine
{
    public function __construct(
        public LineRef $ref,
        public ProductRef $product,
        public string $title,
        public int $quantity,
        public Money $unitPrice,
        public Money $total,
    ) {
        if ($quantity <= 0) {
            throw InvalidCart::nonPositiveQuantity($quantity);
        }

        if ('' === $title) {
            throw InvalidCart::emptyText('Cart line title');
        }

        if (!$unitPrice->currency->equals($total->currency)) {
            throw CurrencyMismatch::between($unitPrice->currency, $total->currency);
        }
    }
}
