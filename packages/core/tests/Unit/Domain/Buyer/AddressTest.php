<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Buyer;

use Lada\Core\Domain\Buyer\Address;
use Lada\Core\Domain\Exception\InvalidBuyer;
use Lada\Core\Domain\Shared\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    #[Test]
    public function it_holds_a_postal_address(): void
    {
        // Arrange
        $country = Country::of('PL');

        // Act
        $address = new Address('Jan Kowalski', 'Marszałkowska 1', 'Warszawa', '00-001', $country, 'm. 2', 'mazowieckie', '+48500100200');

        // Assert
        self::assertSame('Jan Kowalski', $address->name);
        self::assertSame('m. 2', $address->streetLine2);
        self::assertTrue($address->country->equals($country));
    }

    #[Test]
    #[DataProvider('blankRequiredFields')]
    public function it_rejects_a_blank_required_field(string $name, string $street, string $city, string $postalCode): void
    {
        // Arrange
        $country = Country::of('PL');

        // Assert
        $this->expectException(InvalidBuyer::class);

        // Act
        new Address($name, $street, $city, $postalCode, $country);
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function blankRequiredFields(): iterable
    {
        yield 'name' => [' ', 'Marszałkowska 1', 'Warszawa', '00-001'];
        yield 'street' => ['Jan Kowalski', '', 'Warszawa', '00-001'];
        yield 'city' => ['Jan Kowalski', 'Marszałkowska 1', '', '00-001'];
        yield 'postal code' => ['Jan Kowalski', 'Marszałkowska 1', 'Warszawa', ''];
    }
}
