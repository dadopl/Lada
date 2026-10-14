<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\InvalidUrl;
use Lada\Core\Domain\Shared\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    #[Test]
    #[DataProvider('validUrls')]
    public function it_accepts_an_absolute_http_url(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Act
        $url = Url::of($value);

        // Assert
        self::assertSame($value, $url->value);
        self::assertSame($value, (string) $url);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validUrls(): iterable
    {
        yield 'https' => ['https://shop.example/products/red-mug'];
        yield 'http with port' => ['http://localhost:8000/checkout?cart=42'];
    }

    #[Test]
    #[DataProvider('invalidUrls')]
    public function it_rejects_anything_but_an_absolute_http_url(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Assert
        $this->expectException(InvalidUrl::class);

        // Act
        Url::of($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidUrls(): iterable
    {
        yield 'empty' => [''];
        yield 'relative' => ['/products/red-mug'];
        yield 'javascript' => ['javascript://alert(1)'];
        yield 'ftp' => ['ftp://shop.example/file'];
        yield 'no host' => ['https://'];
    }

    #[Test]
    public function it_compares_by_value(): void
    {
        // Arrange
        $a = Url::of('https://shop.example/a');
        $b = Url::of('https://shop.example/a');
        $c = Url::of('https://shop.example/b');

        // Act
        $sameEquals = $a->equals($b);
        $differentEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameEquals);
        self::assertFalse($differentEquals);
    }
}
