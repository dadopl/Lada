<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Payment;

use Lada\Core\Domain\Exception\InvalidPayment;
use Lada\Core\Domain\Payment\PaymentInstrument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymentInstrumentTest extends TestCase
{
    #[Test]
    public function it_carries_the_credential_for_the_payment_handler(): void
    {
        // Arrange
        $token = 'gpay-token-123';

        // Act
        $instrument = new PaymentInstrument('com.google.pay', $token, 'visa', '4242');

        // Assert
        self::assertSame('com.google.pay', $instrument->handlerId);
        self::assertSame('gpay-token-123', $instrument->token);
        self::assertSame('4242', $instrument->lastDigits);
    }

    #[Test]
    public function it_masks_the_token_in_dumps(): void
    {
        // Arrange
        $instrument = new PaymentInstrument('com.google.pay', 'gpay-token-123', 'visa', '4242');

        // Act
        $dump = print_r($instrument, true);

        // Assert
        self::assertStringNotContainsString('gpay-token-123', $dump);
        self::assertStringContainsString('***', $dump);
        self::assertStringContainsString('com.google.pay', $dump);
    }

    #[Test]
    #[DataProvider('emptyFields')]
    public function it_rejects_an_empty_handler_or_token(string $handlerId, string $token): void
    {
        // Arrange
        // (fields from the data provider)

        // Assert
        $this->expectException(InvalidPayment::class);

        // Act
        new PaymentInstrument($handlerId, $token);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function emptyFields(): iterable
    {
        yield 'handler' => ['', 'token'];
        yield 'token' => ['com.google.pay', ''];
    }
}
