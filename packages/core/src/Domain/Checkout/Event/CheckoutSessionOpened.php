<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout\Event;

use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Shared\DomainEvent;
use Lada\Core\Domain\Shared\Protocol;

/**
 * The checkout session was opened by an agent.
 */
final readonly class CheckoutSessionOpened implements DomainEvent
{
    public function __construct(
        public CheckoutSessionId $id,
        public Protocol $protocol,
    ) {}
}
