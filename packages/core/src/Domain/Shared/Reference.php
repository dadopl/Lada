<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidIdentifier;

/**
 * Opaque identifier owned by the shop (cart id, order number, SKU…).
 * Lada stores and passes it back, it never interprets it.
 */
abstract readonly class Reference
{
    private const int MAX_LENGTH = 255;

    final public function __construct(
        public string $value,
    ) {
        if ('' === $value || mb_strlen($value) > self::MAX_LENGTH || trim($value) !== $value) {
            throw InvalidIdentifier::reference(static::class, $value);
        }
    }

    public function equals(self $other): bool
    {
        return $other instanceof static && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
