<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

use Lada\Core\Domain\Shared\Currency;

final class CurrencyMismatch extends \DomainException implements LadaException
{
    public static function between(Currency $expected, Currency $actual): self
    {
        return new self(\sprintf('Expected money in %s, got %s.', $expected->code, $actual->code));
    }
}
