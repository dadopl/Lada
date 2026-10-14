<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout\Event;

use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Shared\DomainEvent;

/**
 * The checkout session was expired before an order was placed.
 */
final readonly class CheckoutSessionExpired implements DomainEvent
{
    public function __construct(
        public CheckoutSessionId $id,
    ) {}
}
