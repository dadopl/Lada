<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;

/**
 * Cart totals exactly as calculated by the shop's pricing engine.
 */
final readonly class Totals
{
    public Currency $currency;

    public function __construct(
        public Money $subtotal,
        public Money $discount,
        public Money $shipping,
        public Money $tax,
        public Money $total,
    ) {
        $this->currency = $total->currency;

        foreach ([$subtotal, $discount, $shipping, $tax] as $amount) {
            if (!$amount->currency->equals($this->currency)) {
                throw CurrencyMismatch::between($this->currency, $amount->currency);
            }
        }
    }
}
