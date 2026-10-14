<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

final class InvalidUrl extends \InvalidArgumentException implements LadaException
{
    public static function notAbsoluteHttp(string $url): self
    {
        return new self(\sprintf('"%s" is not an absolute http(s) URL.', $url));
    }
}
