<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Order;

use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Exception\InvalidOrder;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Order\PlacementOutcome;
use Lada\Core\Domain\Order\PlacementResult;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use Lada\Core\Domain\Shared\Url;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlacementResultTest extends TestCase
{
    #[Test]
    public function it_reports_a_placed_order(): void
    {
        // Arrange
        $orderRef = new OrderRef('2026/10/0001');

        // Act
        $result = PlacementResult::placed($orderRef);

        // Assert
        self::assertSame(PlacementOutcome::Placed, $result->outcome);
        self::assertSame($orderRef, $result->orderRef);
        self::assertTrue($result->isPlaced());
    }

    #[Test]
    public function it_sends_the_buyer_to_finish_the_payment(): void
    {
        // Arrange
        $continueUrl = Url::of('https://psp.example/3ds/abc');

        // Act
        $result = PlacementResult::requiresAction($continueUrl);

        // Assert
        self::assertSame(PlacementOutcome::RequiresAction, $result->outcome);
        self::assertSame($continueUrl, $result->continueUrl);
        self::assertNull($result->orderRef);
        self::assertFalse($result->isPlaced());
    }

    #[Test]
    public function it_reports_a_declined_payment(): void
    {
        // Arrange
        $reason = 'Insufficient funds';

        // Act
        $result = PlacementResult::declined($reason);

        // Assert
        self::assertSame(PlacementOutcome::Declined, $result->outcome);
        self::assertSame('Insufficient funds', $result->reason);
        self::assertFalse($result->isPlaced());
    }

    #[Test]
    public function it_requires_a_reason_for_a_decline(): void
    {
        // Arrange
        $reason = ' ';

        // Assert
        $this->expectException(InvalidOrder::class);

        // Act
        PlacementResult::declined($reason);
    }

    #[Test]
    public function it_returns_the_current_cart_when_the_total_changed(): void
    {
        // Arrange
        $pln = static fn(int $amount): Money => Money::of($amount, Currency::of('PLN'));
        $cart = new CartSnapshot(new CartRef('42'), [], new Totals($pln(0), $pln(0), $pln(0), $pln(0), $pln(0)));

        // Act
        $result = PlacementResult::totalChanged($cart);

        // Assert
        self::assertSame(PlacementOutcome::TotalChanged, $result->outcome);
        self::assertSame($cart, $result->cart);
        self::assertFalse($result->isPlaced());
    }

    #[Test]
    public function it_names_the_lines_that_are_out_of_stock(): void
    {
        // Arrange
        $lines = [new LineRef('1'), new LineRef('3')];

        // Act
        $result = PlacementResult::outOfStock($lines);

        // Assert
        self::assertSame(PlacementOutcome::OutOfStock, $result->outcome);
        self::assertSame($lines, $result->unavailableLines);
        self::assertFalse($result->isPlaced());
    }

    #[Test]
    public function it_requires_at_least_one_line_to_be_out_of_stock(): void
    {
        // Arrange
        $lines = [];

        // Assert
        $this->expectException(InvalidOrder::class);

        // Act
        PlacementResult::outOfStock($lines);
    }
}
