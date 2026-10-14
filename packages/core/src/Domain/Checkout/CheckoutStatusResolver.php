<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout;

use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\Message;

/**
 * Derives the status of an open checkout from the shop's cart: the shop decides what is wrong,
 * Lada only decides who has to act — nobody, the agent or the buyer.
 */
final readonly class CheckoutStatusResolver
{
    public function resolve(CartSnapshot $cart): CheckoutStatus
    {
        if (array_any($cart->messages, static fn(Message $message): bool => $message->severity->requiresBuyer())) {
            return CheckoutStatus::RequiresEscalation;
        }

        $blocked = array_any($cart->messages, static fn(Message $message): bool => $message->severity->blocksCompletion());

        if ($blocked || [] === $cart->lines || [] !== $cart->missing) {
            return CheckoutStatus::Incomplete;
        }

        return CheckoutStatus::ReadyForComplete;
    }
}
