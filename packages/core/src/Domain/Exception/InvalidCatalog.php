<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidCatalog extends \InvalidArgumentException implements LadaException
{
    public static function limitOutOfRange(int $limit, int $max): self
    {
        return new self(\sprintf('Search limit must be between 1 and %d, got %d.', $max, $limit));
    }

    public static function emptyText(string $field): self
    {
        return new self(\sprintf('%s must not be empty.', $field));
    }
}
