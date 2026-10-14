<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidOrder extends \InvalidArgumentException implements LadaException
{
    public static function emptyText(string $field): self
    {
        return new self(\sprintf('%s must not be empty.', $field));
    }

    public static function noUnavailableLines(): self
    {
        return new self('An out-of-stock placement result must name at least one unavailable line.');
    }
}
