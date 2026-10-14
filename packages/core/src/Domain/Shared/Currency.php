<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidCurrency;

/**
 * ISO 4217 alphabetic currency code, e.g. PLN, EUR, USD.
 */
final readonly class Currency
{
    private function __construct(
        public string $code,
    ) {}

    public static function of(string $code): self
    {
        if (1 !== preg_match('/^[A-Z]{3}$/', $code)) {
            throw InvalidCurrency::code($code);
        }

        return new self($code);
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
