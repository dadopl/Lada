<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidCountry;

/**
 * ISO 3166-1 alpha-2 country code, e.g. PL, DE, US.
 */
final readonly class Country
{
    private function __construct(
        public string $code,
    ) {}

    public static function of(string $code): self
    {
        if (1 !== preg_match('/^[A-Z]{2}\z/', $code)) {
            throw InvalidCountry::code($code);
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
