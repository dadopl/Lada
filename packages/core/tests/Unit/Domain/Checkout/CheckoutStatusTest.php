<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Checkout;

use Lada\Core\Domain\Checkout\CheckoutStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckoutStatusTest extends TestCase
{
    #[Test]
    #[DataProvider('statuses')]
    public function it_knows_which_statuses_are_final_and_open(CheckoutStatus $status, bool $final, bool $open): void
    {
        // Arrange
        // (status from the data provider)

        // Act
        $isFinal = $status->isFinal();
        $isOpen = $status->isOpen();

        // Assert
        self::assertSame($final, $isFinal);
        self::assertSame($open, $isOpen);
    }

    /**
     * @return iterable<string, array{CheckoutStatus, bool, bool}>
     */
    public static function statuses(): iterable
    {
        yield 'incomplete' => [CheckoutStatus::Incomplete, false, true];
        yield 'requires escalation' => [CheckoutStatus::RequiresEscalation, false, true];
        yield 'ready for complete' => [CheckoutStatus::ReadyForComplete, false, true];
        yield 'complete in progress' => [CheckoutStatus::CompleteInProgress, false, false];
        yield 'completed' => [CheckoutStatus::Completed, true, false];
        yield 'canceled' => [CheckoutStatus::Canceled, true, false];
        yield 'expired' => [CheckoutStatus::Expired, true, false];
    }
}
