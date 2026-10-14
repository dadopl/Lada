<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout\Event;

use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Shared\DomainEvent;

/**
 * The checkout session was completed with an order in the shop.
 */
final readonly class CheckoutSessionCompleted implements DomainEvent
{
    public function __construct(
        public CheckoutSessionId $id,
        public OrderRef $orderRef,
    ) {}
}
