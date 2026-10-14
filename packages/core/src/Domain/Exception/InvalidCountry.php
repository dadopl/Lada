<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidCountry extends \InvalidArgumentException implements LadaException
{
    public static function code(string $code): self
    {
        return new self(\sprintf('"%s" is not an ISO 3166-1 alpha-2 country code.', $code));
    }
}
