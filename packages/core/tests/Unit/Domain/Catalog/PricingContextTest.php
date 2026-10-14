<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Catalog;

use Lada\Core\Domain\Catalog\PricingContext;
use Lada\Core\Domain\Shared\Channel;
use Lada\Core\Domain\Shared\Country;
use Lada\Core\Domain\Shared\Currency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PricingContextTest extends TestCase
{
    #[Test]
    public function it_ships_to_the_channel_country_by_default(): void
    {
        // Arrange
        $channel = new Channel('default', Currency::of('PLN'), 'pl_PL', Country::of('PL'));

        // Act
        $context = PricingContext::forChannel($channel);

        // Assert
        self::assertSame($channel, $context->channel);
        self::assertTrue($context->shipTo->equals(Country::of('PL')));
    }

    #[Test]
    public function it_can_ship_to_another_country_without_changing_the_original(): void
    {
        // Arrange
        $channel = new Channel('default', Currency::of('PLN'), 'pl_PL', Country::of('PL'));
        $context = PricingContext::forChannel($channel);

        // Act
        $toGermany = $context->shippingTo(Country::of('DE'));

        // Assert
        self::assertTrue($toGermany->shipTo->equals(Country::of('DE')));
        self::assertSame($channel, $toGermany->channel);
        self::assertTrue($context->shipTo->equals(Country::of('PL')));
    }
}
