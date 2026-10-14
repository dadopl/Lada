<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidCart extends \InvalidArgumentException implements LadaException
{
    public static function nonPositiveQuantity(int $quantity): self
    {
        return new self(\sprintf('Cart line quantity must be positive, got %d.', $quantity));
    }

    public static function emptyText(string $field): self
    {
        return new self(\sprintf('%s must not be empty.', $field));
    }

    public static function unknownFulfillmentOption(string $id): self
    {
        return new self(\sprintf('Selected fulfillment option "%s" is not one of the offered options.', $id));
    }

    public static function duplicate(string $what, string $id): self
    {
        return new self(\sprintf('%s "%s" appears more than once in the cart.', $what, $id));
    }
}
