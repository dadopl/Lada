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
    public function it_knows_which_statuses_are_final(CheckoutStatus $status, bool $expected): void
    {
        // Arrange
        // (status from the data provider)

        // Act
        $isFinal = $status->isFinal();

        // Assert
        self::assertSame($expected, $isFinal);
    }

    /**
     * @return iterable<string, array{CheckoutStatus, bool}>
     */
    public static function statuses(): iterable
    {
        yield 'incomplete' => [CheckoutStatus::Incomplete, false];
        yield 'requires escalation' => [CheckoutStatus::RequiresEscalation, false];
        yield 'ready for complete' => [CheckoutStatus::ReadyForComplete, false];
        yield 'complete in progress' => [CheckoutStatus::CompleteInProgress, false];
        yield 'completed' => [CheckoutStatus::Completed, true];
        yield 'canceled' => [CheckoutStatus::Canceled, true];
        yield 'expired' => [CheckoutStatus::Expired, true];
    }
}
