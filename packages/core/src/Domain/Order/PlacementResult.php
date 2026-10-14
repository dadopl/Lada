<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Order;

use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Exception\InvalidOrder;
use Lada\Core\Domain\Shared\Url;

/**
 * What happened when the shop tried to place the order. Lada translates it into protocol statuses and messages.
 * Only the named constructors create it, so each outcome always carries its own data.
 */
final readonly class PlacementResult
{
    /**
     * @param list<LineRef> $unavailableLines
     */
    private function __construct(
        public PlacementOutcome $outcome,
        public ?OrderRef $orderRef = null,
        public ?Url $continueUrl = null,
        public ?string $reason = null,
        public ?CartSnapshot $cart = null,
        public array $unavailableLines = [],
    ) {}

    public static function placed(OrderRef $orderRef): self
    {
        return new self(PlacementOutcome::Placed, orderRef: $orderRef);
    }

    public static function requiresAction(Url $continueUrl): self
    {
        return new self(PlacementOutcome::RequiresAction, continueUrl: $continueUrl);
    }

    /**
     * @param string $reason shown to the buyer, e.g. "Insufficient funds"
     */
    public static function declined(string $reason): self
    {
        if ('' === trim($reason)) {
            throw InvalidOrder::emptyText('Decline reason');
        }

        return new self(PlacementOutcome::Declined, reason: $reason);
    }

    /**
     * @param CartSnapshot $cart the cart with the current prices
     */
    public static function totalChanged(CartSnapshot $cart): self
    {
        return new self(PlacementOutcome::TotalChanged, cart: $cart);
    }

    /**
     * @param list<LineRef> $lines
     */
    public static function outOfStock(array $lines): self
    {
        if ([] === $lines) {
            throw InvalidOrder::noUnavailableLines();
        }

        return new self(PlacementOutcome::OutOfStock, unavailableLines: $lines);
    }

    public function isPlaced(): bool
    {
        return PlacementOutcome::Placed === $this->outcome;
    }
}
