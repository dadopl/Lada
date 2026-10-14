<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidUrl;

/**
 * Absolute http(s) URL, e.g. a product page or a checkout continue URL handed to an agent.
 */
final readonly class Url
{
    private function __construct(
        public string $value,
    ) {}

    public static function of(string $value): self
    {
        $scheme = parse_url($value, \PHP_URL_SCHEME);

        if (false === filter_var($value, \FILTER_VALIDATE_URL) || !\in_array($scheme, ['http', 'https'], true)) {
            throw InvalidUrl::notAbsoluteHttp($value);
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
