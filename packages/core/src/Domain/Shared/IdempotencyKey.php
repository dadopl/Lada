<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidIdentifier;

/**
 * Key sent by the agent (Idempotency-Key header) to make a mutation safe to retry.
 * It is forwarded to the shop, so the payment gateway can deduplicate charges too.
 */
final readonly class IdempotencyKey
{
    private const string PATTERN = '/^[\x21-\x7E]{1,255}\z/';

    private function __construct(
        public string $value,
    ) {}

    public static function fromString(string $value): self
    {
        if (1 !== preg_match(self::PATTERN, $value)) {
            throw InvalidIdentifier::idempotencyKey($value);
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
