<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Buyer;

use Lada\Core\Domain\Buyer\Email;
use Lada\Core\Domain\Exception\InvalidBuyer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    #[DataProvider('validEmails')]
    public function it_accepts_a_valid_address(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Act
        $email = Email::of($value);

        // Assert
        self::assertSame($value, $email->value);
        self::assertSame($value, (string) $email);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validEmails(): iterable
    {
        yield 'plain' => ['jan@example.com'];
        yield 'plus tag' => ['jan+agent@example.com'];
        yield 'unicode local part' => ['józef@example.pl'];
    }

    #[Test]
    #[DataProvider('invalidEmails')]
    public function it_rejects_an_invalid_address(string $value): void
    {
        // Arrange
        // (value from the data provider)

        // Assert
        $this->expectException(InvalidBuyer::class);

        // Act
        Email::of($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidEmails(): iterable
    {
        yield 'empty' => [''];
        yield 'no at sign' => ['jan.example.com'];
        yield 'no domain' => ['jan@'];
        yield 'trailing newline' => ["jan@example.com\n"];
    }

    #[Test]
    public function it_compares_case_insensitively(): void
    {
        // Arrange
        $a = Email::of('Jan@Example.com');
        $b = Email::of('jan@example.com');
        $c = Email::of('anna@example.com');

        // Act
        $sameEquals = $a->equals($b);
        $differentEquals = $a->equals($c);

        // Assert
        self::assertTrue($sameEquals);
        self::assertFalse($differentEquals);
    }
}
