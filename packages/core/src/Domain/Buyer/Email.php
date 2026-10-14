<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Buyer;

use Lada\Core\Domain\Exception\InvalidBuyer;

final readonly class Email
{
    private function __construct(
        public string $value,
    ) {}

    public static function of(string $value): self
    {
        if (false === filter_var($value, \FILTER_VALIDATE_EMAIL, \FILTER_FLAG_EMAIL_UNICODE)) {
            throw InvalidBuyer::email($value);
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
