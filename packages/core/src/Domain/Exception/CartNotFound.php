<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

use Lada\Core\Domain\Cart\CartRef;

final class CartNotFound extends \RuntimeException implements LadaException
{
    public static function withRef(CartRef $ref): self
    {
        return new self(\sprintf('Cart "%s" does not exist in the shop.', $ref->value));
    }
}
