<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidChannel;

/**
 * A sales channel of the shop: what agents see, in which currency, language and market.
 */
final readonly class Channel
{
    public function __construct(
        public string $code,
        public Currency $currency,
        public string $locale,
        public Country $country,
    ) {
        if (1 !== preg_match('/^[a-z0-9_-]{1,64}\z/', $code)) {
            throw InvalidChannel::code($code);
        }

        if (1 !== preg_match('/^[a-z]{2,3}(_[A-Z]{2})?\z/', $locale)) {
            throw InvalidChannel::locale($locale);
        }
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
