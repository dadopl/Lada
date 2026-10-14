<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidPayment extends \InvalidArgumentException implements LadaException
{
    public static function emptyText(string $field): self
    {
        return new self(\sprintf('%s must not be empty.', $field));
    }
}
