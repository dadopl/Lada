<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Application\Payment;

use Lada\Core\Application\Exception\DuplicatePaymentHandler;
use Lada\Core\Application\Exception\UnknownPaymentHandler;
use Lada\Core\Application\Payment\PaymentHandlerRegistry;
use Lada\Core\Application\Port\PaymentHandler;
use Lada\Core\Domain\Payment\PaymentInstrument;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymentHandlerRegistryTest extends TestCase
{
    #[Test]
    public function it_finds_the_handler_that_issued_the_instrument(): void
    {
        // Arrange
        $googlePay = $this->handler('com.google.pay');
        $registry = new PaymentHandlerRegistry([$this->handler('dev.acp.tokenized.card'), $googlePay]);

        // Act
        $handler = $registry->forInstrument(new PaymentInstrument('com.google.pay', 'token'));

        // Assert
        self::assertSame($googlePay, $handler);
    }

    #[Test]
    public function it_rejects_an_instrument_from_an_unknown_handler(): void
    {
        // Arrange
        $registry = new PaymentHandlerRegistry([$this->handler('com.google.pay')]);

        // Assert
        $this->expectException(UnknownPaymentHandler::class);
        $this->expectExceptionMessage('"com.apple.pay"');

        // Act
        $registry->forInstrument(new PaymentInstrument('com.apple.pay', 'token'));
    }

    #[Test]
    public function it_lists_all_handlers_in_registration_order(): void
    {
        // Arrange
        $googlePay = $this->handler('com.google.pay');
        $card = $this->handler('dev.acp.tokenized.card');
        $registry = new PaymentHandlerRegistry(new \ArrayIterator([$googlePay, $card]));

        // Act
        $all = $registry->all();

        // Assert
        self::assertSame([$googlePay, $card], $all);
    }

    #[Test]
    public function it_is_empty_without_handlers(): void
    {
        // Arrange
        $registry = new PaymentHandlerRegistry([]);

        // Act
        $all = $registry->all();

        // Assert
        self::assertSame([], $all);
    }

    #[Test]
    public function it_rejects_two_handlers_with_the_same_id(): void
    {
        // Arrange
        $handlers = [$this->handler('com.google.pay'), $this->handler('com.google.pay')];

        // Assert
        $this->expectException(DuplicatePaymentHandler::class);

        // Act
        new PaymentHandlerRegistry($handlers);
    }

    private function handler(string $id): PaymentHandler
    {
        $handler = self::createStub(PaymentHandler::class);
        $handler->method('id')->willReturn($id);

        return $handler;
    }
}
