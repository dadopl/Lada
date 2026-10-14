<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidBuyer extends \InvalidArgumentException implements LadaException
{
    public static function email(string $email): self
    {
        return new self(\sprintf('"%s" is not a valid e-mail address.', $email));
    }

    public static function emptyText(string $field): self
    {
        return new self(\sprintf('%s must not be empty.', $field));
    }
}
