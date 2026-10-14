<?php

declare(strict_types=1);

namespace Lada\Core\Application\Exception;

use Lada\Core\Domain\Exception\LadaException;

final class UnknownPaymentHandler extends \RuntimeException implements LadaException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf('No payment handler "%s" is registered.', $id));
    }
}
