<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidChannel extends \InvalidArgumentException implements LadaException
{
    public static function code(string $code): self
    {
        return new self(\sprintf('Channel code must be 1-64 characters of [a-z0-9_-], got "%s".', $code));
    }

    public static function locale(string $locale): self
    {
        return new self(\sprintf('"%s" is not a locale like "pl" or "pl_PL".', $locale));
    }
}
