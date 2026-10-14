<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

use Lada\Core\Domain\Order\OrderRef;

final class OrderNotFound extends \RuntimeException implements LadaException
{
    public static function withRef(OrderRef $ref): self
    {
        return new self(\sprintf('Order "%s" does not exist in the shop.', $ref->value));
    }
}
