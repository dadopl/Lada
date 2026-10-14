<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Buyer\Address;
use Lada\Core\Domain\Buyer\Buyer;

/**
 * The complete cart state the agent asks for. The shop maps it onto its own cart
 * (full replacement, not a patch) and answers with a CartSnapshot.
 */
final readonly class CartDraft
{
    /**
     * @param list<DraftLine> $lines
     * @param list<string>    $discountCodes
     */
    public function __construct(
        public array $lines,
        public Buyer $buyer = new Buyer(),
        public ?Address $shippingAddress = null,
        public ?Address $billingAddress = null,
        public ?string $fulfillmentOptionId = null,
        public array $discountCodes = [],
    ) {}
}
