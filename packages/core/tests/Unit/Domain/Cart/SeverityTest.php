<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\Severity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeverityTest extends TestCase
{
    #[Test]
    #[DataProvider('severities')]
    public function it_classifies_the_impact_on_the_purchase(Severity $severity, bool $blocks, bool $requiresBuyer): void
    {
        // Arrange
        // (severity from the data provider)

        // Act
        $blocksCompletion = $severity->blocksCompletion();
        $needsBuyer = $severity->requiresBuyer();

        // Assert
        self::assertSame($blocks, $blocksCompletion);
        self::assertSame($requiresBuyer, $needsBuyer);
    }

    /**
     * @return iterable<string, array{Severity, bool, bool}>
     */
    public static function severities(): iterable
    {
        yield 'info' => [Severity::Info, false, false];
        yield 'warning' => [Severity::Warning, false, false];
        yield 'recoverable' => [Severity::Recoverable, true, false];
        yield 'requires buyer input' => [Severity::RequiresBuyerInput, true, true];
        yield 'requires buyer review' => [Severity::RequiresBuyerReview, true, true];
    }
}
