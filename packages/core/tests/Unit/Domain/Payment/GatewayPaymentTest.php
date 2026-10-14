<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Payment;

use Lada\Core\Domain\Exception\InvalidPayment;
use Lada\Core\Domain\Payment\GatewayPayment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GatewayPaymentTest extends TestCase
{
    #[Test]
    public function it_describes_a_payment_in_the_shop_terms(): void
    {
        // Arrange
        $data = ['payment_method_data' => ['shared_payment_granted_token' => 'spt_123']];

        // Act
        $payment = new GatewayPayment('stripe', $data);

        // Assert
        self::assertSame('stripe', $payment->method);
        self::assertSame($data, $payment->data);
    }

    #[Test]
    public function it_masks_gateway_data_in_dumps(): void
    {
        // Arrange
        $payment = new GatewayPayment('stripe', ['payment_method_data' => ['shared_payment_granted_token' => 'spt_123']]);

        // Act
        $dump = print_r($payment, true);

        // Assert
        self::assertStringNotContainsString('spt_123', $dump);
        self::assertStringContainsString('payment_method_data', $dump);
        self::assertStringContainsString('stripe', $dump);
    }

    #[Test]
    public function it_rejects_an_empty_method(): void
    {
        // Arrange
        $method = '';

        // Assert
        $this->expectException(InvalidPayment::class);

        // Act
        new GatewayPayment($method);
    }
}
