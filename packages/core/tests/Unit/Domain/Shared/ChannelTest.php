<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidChannel;
use Lada\Core\Domain\Shared\Channel;
use Lada\Core\Domain\Shared\Country;
use Lada\Core\Domain\Shared\Currency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelTest extends TestCase
{
    #[Test]
    #[DataProvider('validLocales')]
    public function it_describes_a_sales_channel(string $locale): void
    {
        // Arrange
        $currency = Currency::of('PLN');
        $country = Country::of('PL');

        // Act
        $channel = new Channel('default', $currency, $locale, $country);

        // Assert
        self::assertSame('default', $channel->code);
        self::assertSame($locale, $channel->locale);
        self::assertTrue($channel->currency->equals($currency));
        self::assertTrue($channel->country->equals($country));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validLocales(): iterable
    {
        yield 'language' => ['pl'];
        yield 'language and region' => ['pl_PL'];
        yield 'three-letter language' => ['fil_PH'];
    }

    #[Test]
    #[DataProvider('invalidChannels')]
    public function it_rejects_an_invalid_code_or_locale(string $code, string $locale): void
    {
        // Arrange
        $currency = Currency::of('PLN');
        $country = Country::of('PL');

        // Assert
        $this->expectException(InvalidChannel::class);

        // Act
        new Channel($code, $currency, $locale, $country);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidChannels(): iterable
    {
        yield 'empty code' => ['', 'pl_PL'];
        yield 'code with space' => ['web shop', 'pl_PL'];
        yield 'uppercase code' => ['DEFAULT', 'pl_PL'];
        yield 'code too long' => [str_repeat('a', 65), 'pl_PL'];
        yield 'empty locale' => ['default', ''];
        yield 'dash locale' => ['default', 'pl-PL'];
        yield 'uppercase language' => ['default', 'PL_PL'];
    }

    #[Test]
    public function it_is_identified_by_its_code(): void
    {
        // Arrange
        $a = new Channel('default', Currency::of('PLN'), 'pl_PL', Country::of('PL'));
        $b = new Channel('default', Currency::of('EUR'), 'de_DE', Country::of('DE'));
        $c = new Channel('b2b', Currency::of('PLN'), 'pl_PL', Country::of('PL'));

        // Act
        $sameCodeEquals = $a->equals($b);
        $otherCodeEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameCodeEquals);
        self::assertFalse($otherCodeEquals);
    }
}
