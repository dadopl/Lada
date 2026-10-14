<?php

declare(strict_types=1);

namespace Lada\Core\Application\Exception;

use Lada\Core\Domain\Exception\LadaException;

final class DuplicatePaymentHandler extends \LogicException implements LadaException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf('Payment handler "%s" is registered more than once.', $id));
    }
}
