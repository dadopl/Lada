<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout;

use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionCanceled;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionCompleted;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionExpired;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionOpened;
use Lada\Core\Domain\Exception\CheckoutNotAllowed;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Shared\DomainEvent;
use Lada\Core\Domain\Shared\Money;
use Lada\Core\Domain\Shared\Protocol;
use Lada\Core\Domain\Shared\Url;

/**
 * A purchase an agent is making. Thin by design: it points at the shop's cart and derives its status
 * from the cart; it never copies line items or computes totals.
 */
final class CheckoutSession
{
    private CheckoutStatus $status;

    /** Total of the last cart shown to the agent: the amount the agent accepts when it completes. */
    private Money $total;

    private ?OrderRef $orderRef = null;

    private ?Url $continueUrl = null;

    /** @var list<DomainEvent> */
    private array $events = [];

    private function __construct(
        public readonly CheckoutSessionId $id,
        public readonly Protocol $protocol,
        public readonly CartRef $cartRef,
        public readonly \DateTimeImmutable $expiresAt,
        CartSnapshot $cart,
    ) {
        $this->applyCart($cart);
    }

    public static function open(
        CheckoutSessionId $id,
        Protocol $protocol,
        CartSnapshot $cart,
        \DateTimeImmutable $expiresAt,
    ): self {
        $session = new self($id, $protocol, $cart->ref, $expiresAt, $cart);
        $session->events[] = new CheckoutSessionOpened($id, $protocol);

        return $session;
    }

    public function status(): CheckoutStatus
    {
        return $this->status;
    }

    public function total(): Money
    {
        return $this->total;
    }

    public function orderRef(): ?OrderRef
    {
        return $this->orderRef;
    }

    public function continueUrl(): ?Url
    {
        return $this->continueUrl;
    }

    public function isExpiredAt(\DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }

    /**
     * The agent changed the cart; the status follows the shop's new snapshot.
     */
    public function updateCart(CartSnapshot $cart, \DateTimeImmutable $now): void
    {
        $this->assertOpen('update', $now);
        $this->assertOwnCart($cart);

        $this->applyCart($cart);
    }

    /**
     * Locks the session while the shop places the order; only a ready session can be completed.
     */
    public function startCompletion(\DateTimeImmutable $now): void
    {
        $this->assertOpen('complete', $now);

        if (CheckoutStatus::ReadyForComplete !== $this->status) {
            throw CheckoutNotAllowed::inStatus('complete', $this->status);
        }

        $this->status = CheckoutStatus::CompleteInProgress;
    }

    /**
     * Placement did not create an order (declined, total changed, out of stock); unlock with the current cart.
     */
    public function abortCompletion(CartSnapshot $cart): void
    {
        $this->assertCompletionInProgress('abort completion of');
        $this->assertOwnCart($cart);

        $this->applyCart($cart);
    }

    /**
     * The buyer has to finish the payment outside the agent, e.g. 3-D Secure or a hosted payment page.
     */
    public function requireBuyerAction(Url $continueUrl): void
    {
        $this->assertCompletionInProgress('hand over');

        $this->status = CheckoutStatus::RequiresEscalation;
        $this->continueUrl = $continueUrl;
    }

    /**
     * The shop placed the order — through the agent, or after the buyer finished on the shop's site.
     */
    public function complete(OrderRef $orderRef): void
    {
        if ($this->status->isFinal()) {
            throw CheckoutNotAllowed::inStatus('complete', $this->status);
        }

        $this->status = CheckoutStatus::Completed;
        $this->orderRef = $orderRef;
        $this->events[] = new CheckoutSessionCompleted($this->id, $orderRef);
    }

    public function cancel(): void
    {
        if (!$this->status->isOpen()) {
            throw CheckoutNotAllowed::inStatus('cancel', $this->status);
        }

        $this->status = CheckoutStatus::Canceled;
        $this->events[] = new CheckoutSessionCanceled($this->id);
    }

    public function expire(\DateTimeImmutable $now): void
    {
        if (!$this->status->isOpen()) {
            throw CheckoutNotAllowed::inStatus('expire', $this->status);
        }

        if (!$this->isExpiredAt($now)) {
            throw CheckoutNotAllowed::notExpiredYet($this->id);
        }

        $this->status = CheckoutStatus::Expired;
        $this->events[] = new CheckoutSessionExpired($this->id);
    }

    /**
     * @return list<DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function applyCart(CartSnapshot $cart): void
    {
        $this->status = new CheckoutStatusResolver()->resolve($cart);
        $this->total = $cart->totals->total;
    }

    private function assertOpen(string $operation, \DateTimeImmutable $now): void
    {
        if (!$this->status->isOpen()) {
            throw CheckoutNotAllowed::inStatus($operation, $this->status);
        }

        if ($this->isExpiredAt($now)) {
            throw CheckoutNotAllowed::expired($this->id);
        }
    }

    private function assertCompletionInProgress(string $operation): void
    {
        if (CheckoutStatus::CompleteInProgress !== $this->status) {
            throw CheckoutNotAllowed::inStatus($operation, $this->status);
        }
    }

    private function assertOwnCart(CartSnapshot $cart): void
    {
        if (!$cart->ref->equals($this->cartRef)) {
            throw CheckoutNotAllowed::foreignCart($this->id, $cart->ref);
        }
    }
}
