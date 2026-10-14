<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidCountry;
use Lada\Core\Domain\Shared\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
    #[Test]
    public function it_is_created_from_an_iso_code(): void
    {
        // Arrange
        $code = 'PL';

        // Act
        $country = Country::of($code);

        // Assert
        self::assertSame('PL', $country->code);
        self::assertSame('PL', (string) $country);
    }

    #[Test]
    #[DataProvider('invalidCodes')]
    public function it_rejects_a_code_that_is_not_iso_3166_alpha_2(string $code): void
    {
        // Arrange
        // (code from the data provider)

        // Assert
        $this->expectException(InvalidCountry::class);

        // Act
        Country::of($code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'empty' => [''];
        yield 'lowercase' => ['pl'];
        yield 'alpha-3' => ['POL'];
        yield 'trailing newline' => ["PL\n"];
    }

    #[Test]
    public function it_compares_by_code(): void
    {
        // Arrange
        $a = Country::of('PL');
        $b = Country::of('PL');
        $c = Country::of('DE');

        // Act
        $sameEquals = $a->equals($b);
        $differentEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameEquals);
        self::assertFalse($differentEquals);
    }
}
