<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidIdentifier extends \InvalidArgumentException implements LadaException
{
    public static function reference(string $type, string $value): self
    {
        return new self(\sprintf('%s must be 1-255 characters without surrounding whitespace, got "%s".', $type, $value));
    }

    public static function checkoutSessionId(string $value): self
    {
        return new self(\sprintf('Checkout session id must be a UUID, got "%s".', $value));
    }

    public static function idempotencyKey(string $value): self
    {
        return new self(\sprintf('Idempotency key must be 1-255 visible ASCII characters, got "%s".', $value));
    }
}
