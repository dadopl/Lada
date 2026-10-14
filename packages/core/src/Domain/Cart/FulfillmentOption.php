<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Money;

/**
 * A shipping or pickup option offered by the shop for the current cart.
 */
final readonly class FulfillmentOption
{
    public function __construct(
        public string $id,
        public string $title,
        public Money $price,
        public ?string $deliveryEstimate = null,
    ) {
        if ('' === $id) {
            throw InvalidCart::emptyText('Fulfillment option id');
        }

        if ('' === $title) {
            throw InvalidCart::emptyText('Fulfillment option title');
        }
    }
}
