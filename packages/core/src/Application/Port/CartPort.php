<?php

declare(strict_types=1);

namespace Lada\Core\Application\Port;

use Lada\Core\Domain\Cart\CartDraft;
use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Catalog\PricingContext;
use Lada\Core\Domain\Exception\CartNotFound;

/**
 * Implemented by the shop on top of its own cart and pricing engine.
 */
interface CartPort
{
    public function create(CartDraft $draft, PricingContext $context): CartSnapshot;

    /**
     * Full replacement of the cart state; the shop maps it onto its own steps.
     * Must be idempotent: replacing with the same draft twice gives the same snapshot.
     *
     * @throws CartNotFound
     */
    public function replace(CartRef $ref, CartDraft $draft): CartSnapshot;

    /**
     * @throws CartNotFound
     */
    public function get(CartRef $ref): CartSnapshot;

    /**
     * Called when the checkout session is canceled or expires; the shop may delete the cart.
     */
    public function release(CartRef $ref): void;
}
