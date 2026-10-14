<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Catalog;

use Lada\Core\Domain\Shared\Channel;
use Lada\Core\Domain\Shared\Country;

/**
 * What the shop needs to price products for an agent: the channel and the country the goods ship to
 * (taxes and availability may depend on it).
 */
final readonly class PricingContext
{
    private function __construct(
        public Channel $channel,
        public Country $shipTo,
    ) {}

    public static function forChannel(Channel $channel): self
    {
        return new self($channel, $channel->country);
    }

    public function shippingTo(Country $country): self
    {
        return new self($this->channel, $country);
    }
}
