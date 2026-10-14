<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidCurrency extends \InvalidArgumentException implements LadaException
{
    public static function code(string $code): self
    {
        return new self(\sprintf('"%s" is not an ISO 4217 currency code.', $code));
    }
}
