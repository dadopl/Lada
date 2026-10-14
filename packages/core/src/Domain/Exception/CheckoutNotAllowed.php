<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Checkout\CheckoutStatus;

/**
 * An operation the checkout session refuses in its current state.
 */
final class CheckoutNotAllowed extends \DomainException implements LadaException
{
    public static function inStatus(string $operation, CheckoutStatus $status): self
    {
        return new self(\sprintf('Cannot %s a checkout session that is %s.', $operation, $status->value));
    }

    public static function expired(CheckoutSessionId $id): self
    {
        return new self(\sprintf('Checkout session "%s" has expired.', $id->value));
    }

    public static function notExpiredYet(CheckoutSessionId $id): self
    {
        return new self(\sprintf('Checkout session "%s" has not expired yet.', $id->value));
    }

    public static function foreignCart(CheckoutSessionId $id, CartRef $cart): self
    {
        return new self(\sprintf('Cart "%s" does not belong to checkout session "%s".', $cart->value, $id->value));
    }
}
