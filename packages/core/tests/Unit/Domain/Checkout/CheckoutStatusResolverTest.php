<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Checkout;

use Lada\Core\Domain\Cart\CartLine;
use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Cart\Message;
use Lada\Core\Domain\Cart\Requirement;
use Lada\Core\Domain\Cart\Severity;
use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Checkout\CheckoutStatus;
use Lada\Core\Domain\Checkout\CheckoutStatusResolver;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckoutStatusResolverTest extends TestCase
{
    #[Test]
    public function it_is_ready_for_complete_when_nothing_is_missing_or_blocking(): void
    {
        // Arrange
        $cart = self::cart(messages: [
            new Message(Severity::Info, 'free_shipping', 'Free shipping.'),
            new Message(Severity::Warning, 'low_stock', 'Only 2 left.'),
        ]);

        // Act
        $status = new CheckoutStatusResolver()->resolve($cart);

        // Assert
        self::assertSame(CheckoutStatus::ReadyForComplete, $status);
    }

    #[Test]
    public function it_is_incomplete_when_the_shop_still_needs_data(): void
    {
        // Arrange
        $cart = self::cart(missing: [Requirement::ShippingAddress]);

        // Act
        $status = new CheckoutStatusResolver()->resolve($cart);

        // Assert
        self::assertSame(CheckoutStatus::Incomplete, $status);
    }

    #[Test]
    public function it_is_incomplete_when_the_cart_is_empty(): void
    {
        // Arrange
        $cart = self::cart(withLine: false);

        // Act
        $status = new CheckoutStatusResolver()->resolve($cart);

        // Assert
        self::assertSame(CheckoutStatus::Incomplete, $status);
    }

    #[Test]
    public function it_is_incomplete_when_a_problem_the_agent_can_fix_blocks_the_purchase(): void
    {
        // Arrange
        $cart = self::cart(messages: [new Message(Severity::Recoverable, 'out_of_stock', 'Out of stock.')]);

        // Act
        $status = new CheckoutStatusResolver()->resolve($cart);

        // Assert
        self::assertSame(CheckoutStatus::Incomplete, $status);
    }

    #[Test]
    #[DataProvider('buyerSeverities')]
    public function it_escalates_to_the_buyer_even_if_other_data_is_missing(Severity $severity): void
    {
        // Arrange
        $cart = self::cart(
            messages: [
                new Message(Severity::Recoverable, 'out_of_stock', 'Out of stock.'),
                new Message($severity, 'age_check', 'Confirm you are over 18.'),
            ],
            missing: [Requirement::BuyerEmail],
        );

        // Act
        $status = new CheckoutStatusResolver()->resolve($cart);

        // Assert
        self::assertSame(CheckoutStatus::RequiresEscalation, $status);
    }

    /**
     * @return iterable<string, array{Severity}>
     */
    public static function buyerSeverities(): iterable
    {
        yield 'buyer input' => [Severity::RequiresBuyerInput];
        yield 'buyer review' => [Severity::RequiresBuyerReview];
    }

    /**
     * @param list<Message>     $messages
     * @param list<Requirement> $missing
     */
    private static function cart(array $messages = [], array $missing = [], bool $withLine = true): CartSnapshot
    {
        $pln = static fn(int $amount): Money => Money::of($amount, Currency::of('PLN'));
        $lines = $withLine
            ? [new CartLine(new LineRef('1'), new ProductRef('MUG-RED'), 'Red mug', 1, $pln(3900), $pln(3900))]
            : [];

        return new CartSnapshot(
            ref: new CartRef('42'),
            lines: $lines,
            totals: new Totals($pln(3900), $pln(0), $pln(0), $pln(729), $pln(3900)),
            messages: $messages,
            missing: $missing,
        );
    }
}
