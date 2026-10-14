<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Checkout;

use Lada\Core\Domain\Cart\CartLine;
use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Cart\LineRef;
use Lada\Core\Domain\Cart\Requirement;
use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Checkout\CheckoutSession;
use Lada\Core\Domain\Checkout\CheckoutSessionId;
use Lada\Core\Domain\Checkout\CheckoutStatus;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionCanceled;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionCompleted;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionExpired;
use Lada\Core\Domain\Checkout\Event\CheckoutSessionOpened;
use Lada\Core\Domain\Exception\CheckoutNotAllowed;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use Lada\Core\Domain\Shared\Protocol;
use Lada\Core\Domain\Shared\Url;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckoutSessionTest extends TestCase
{
    private const string NOW = '2026-10-14 12:00:00';
    private const string EXPIRES_AT = '2026-10-14 18:00:00';

    #[Test]
    public function it_opens_with_the_status_and_total_of_the_cart(): void
    {
        // Arrange
        $id = CheckoutSessionId::generate();
        $cart = self::readyCart(8500);

        // Act
        $session = CheckoutSession::open($id, Protocol::Ucp, $cart, self::at(self::EXPIRES_AT));

        // Assert
        self::assertSame($id, $session->id);
        self::assertSame(Protocol::Ucp, $session->protocol);
        self::assertTrue($session->cartRef->equals(new CartRef('42')));
        self::assertSame(CheckoutStatus::ReadyForComplete, $session->status());
        self::assertTrue($session->total()->equals(self::pln(8500)));
        self::assertNull($session->orderRef());
        self::assertNull($session->continueUrl());
    }

    #[Test]
    public function it_records_that_it_was_opened(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Act
        $events = $session->releaseEvents();

        // Assert
        self::assertEquals([new CheckoutSessionOpened($session->id, Protocol::Ucp)], $events);
    }

    #[Test]
    public function it_releases_events_only_once(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->releaseEvents();

        // Act
        $events = $session->releaseEvents();

        // Assert
        self::assertSame([], $events);
    }

    #[Test]
    public function it_follows_the_cart_when_the_agent_updates_it(): void
    {
        // Arrange
        $session = self::session(self::incompleteCart());

        // Act
        $session->updateCart(self::readyCart(9900), self::at(self::NOW));

        // Assert
        self::assertSame(CheckoutStatus::ReadyForComplete, $session->status());
        self::assertTrue($session->total()->equals(self::pln(9900)));
    }

    #[Test]
    public function it_refuses_a_cart_of_another_session(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $foreignCart = self::readyCart(cartRef: '99');

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('Cart "99"');

        // Act
        $session->updateCart($foreignCart, self::at(self::NOW));
    }

    #[Test]
    public function it_refuses_an_update_after_it_expired(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('has expired');

        // Act
        $session->updateCart(self::readyCart(), self::at(self::EXPIRES_AT));
    }

    #[Test]
    public function it_refuses_an_update_while_the_order_is_being_placed(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('Cannot update a checkout session that is complete_in_progress.');

        // Act
        $session->updateCart(self::readyCart(), self::at(self::NOW));
    }

    #[Test]
    public function it_locks_itself_when_completion_starts(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Act
        $session->startCompletion(self::at(self::NOW));

        // Assert
        self::assertSame(CheckoutStatus::CompleteInProgress, $session->status());
    }

    #[Test]
    public function it_refuses_to_complete_when_not_ready(): void
    {
        // Arrange
        $session = self::session(self::incompleteCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('Cannot complete a checkout session that is incomplete.');

        // Act
        $session->startCompletion(self::at(self::NOW));
    }

    #[Test]
    public function it_refuses_to_complete_after_it_expired(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('has expired');

        // Act
        $session->startCompletion(self::at(self::EXPIRES_AT));
    }

    #[Test]
    public function it_refuses_a_second_completion_running_in_parallel(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->startCompletion(self::at(self::NOW));
    }

    #[Test]
    public function it_completes_with_the_order_placed_by_the_shop(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));
        $session->releaseEvents();
        $orderRef = new OrderRef('2026/10/0001');

        // Act
        $session->complete($orderRef);

        // Assert
        self::assertSame(CheckoutStatus::Completed, $session->status());
        self::assertSame($orderRef, $session->orderRef());
        self::assertEquals([new CheckoutSessionCompleted($session->id, $orderRef)], $session->releaseEvents());
    }

    #[Test]
    public function it_completes_when_the_buyer_finished_on_the_shop_site(): void
    {
        // Arrange
        $session = self::session(self::incompleteCart());

        // Act
        $session->complete(new OrderRef('2026/10/0001'));

        // Assert
        self::assertSame(CheckoutStatus::Completed, $session->status());
    }

    #[Test]
    public function it_refuses_to_complete_twice(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->complete(new OrderRef('2026/10/0001'));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->complete(new OrderRef('2026/10/0002'));
    }

    #[Test]
    public function it_unlocks_with_the_current_cart_when_placement_fails(): void
    {
        // Arrange
        $session = self::session(self::readyCart(8500));
        $session->startCompletion(self::at(self::NOW));

        // Act
        $session->abortCompletion(self::readyCart(9900));

        // Assert
        self::assertSame(CheckoutStatus::ReadyForComplete, $session->status());
        self::assertTrue($session->total()->equals(self::pln(9900)));
    }

    #[Test]
    public function it_refuses_to_abort_a_completion_that_did_not_start(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->abortCompletion(self::readyCart());
    }

    #[Test]
    public function it_refuses_to_abort_with_a_cart_of_another_session(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->abortCompletion(self::readyCart(cartRef: '99'));
    }

    #[Test]
    public function it_hands_over_to_the_buyer_when_the_payment_needs_their_action(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));
        $continueUrl = Url::of('https://psp.example/3ds/abc');

        // Act
        $session->requireBuyerAction($continueUrl);

        // Assert
        self::assertSame(CheckoutStatus::RequiresEscalation, $session->status());
        self::assertSame($continueUrl, $session->continueUrl());
    }

    #[Test]
    public function it_hands_over_only_during_completion(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->requireBuyerAction(Url::of('https://psp.example/3ds/abc'));
    }

    #[Test]
    public function it_cancels_an_open_session(): void
    {
        // Arrange
        $session = self::session(self::incompleteCart());
        $session->releaseEvents();

        // Act
        $session->cancel();

        // Assert
        self::assertSame(CheckoutStatus::Canceled, $session->status());
        self::assertEquals([new CheckoutSessionCanceled($session->id)], $session->releaseEvents());
    }

    #[Test]
    public function it_refuses_to_cancel_after_the_order_was_placed(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->complete(new OrderRef('2026/10/0001'));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('Cannot cancel a checkout session that is completed.');

        // Act
        $session->cancel();
    }

    #[Test]
    public function it_refuses_to_cancel_while_the_order_is_being_placed(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->cancel();
    }

    #[Test]
    public function it_knows_when_it_expired(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Act
        $beforeExpiry = $session->isExpiredAt(self::at(self::NOW));
        $atExpiry = $session->isExpiredAt(self::at(self::EXPIRES_AT));

        // Assert
        self::assertFalse($beforeExpiry);
        self::assertTrue($atExpiry);
    }

    #[Test]
    public function it_expires_an_open_session_past_its_expiry_time(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->releaseEvents();

        // Act
        $session->expire(self::at(self::EXPIRES_AT));

        // Assert
        self::assertSame(CheckoutStatus::Expired, $session->status());
        self::assertEquals([new CheckoutSessionExpired($session->id)], $session->releaseEvents());
    }

    #[Test]
    public function it_refuses_to_expire_before_its_expiry_time(): void
    {
        // Arrange
        $session = self::session(self::readyCart());

        // Assert
        $this->expectException(CheckoutNotAllowed::class);
        $this->expectExceptionMessage('has not expired yet');

        // Act
        $session->expire(self::at(self::NOW));
    }

    #[Test]
    public function it_refuses_to_expire_while_the_order_is_being_placed(): void
    {
        // Arrange
        $session = self::session(self::readyCart());
        $session->startCompletion(self::at(self::NOW));

        // Assert
        $this->expectException(CheckoutNotAllowed::class);

        // Act
        $session->expire(self::at(self::EXPIRES_AT));
    }

    private static function session(CartSnapshot $cart): CheckoutSession
    {
        return CheckoutSession::open(CheckoutSessionId::generate(), Protocol::Ucp, $cart, self::at(self::EXPIRES_AT));
    }

    private static function readyCart(int $total = 8500, string $cartRef = '42'): CartSnapshot
    {
        return self::cart($total, $cartRef, []);
    }

    private static function incompleteCart(): CartSnapshot
    {
        return self::cart(8500, '42', [Requirement::ShippingAddress]);
    }

    /**
     * @param list<Requirement> $missing
     */
    private static function cart(int $total, string $cartRef, array $missing): CartSnapshot
    {
        return new CartSnapshot(
            ref: new CartRef($cartRef),
            lines: [new CartLine(new LineRef('1'), new ProductRef('MUG-RED'), 'Red mug', 1, self::pln($total), self::pln($total))],
            totals: new Totals(self::pln($total), self::pln(0), self::pln(0), self::pln(0), self::pln($total)),
            missing: $missing,
        );
    }

    private static function pln(int $amount): Money
    {
        return Money::of($amount, Currency::of('PLN'));
    }

    private static function at(string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time);
    }
}
