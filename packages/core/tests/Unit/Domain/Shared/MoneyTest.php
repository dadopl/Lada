<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Shared\Currency;
use Lada\Core\Domain\Shared\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[Test]
    public function it_holds_an_amount_in_minor_units(): void
    {
        // Arrange
        $currency = Currency::of('PLN');

        // Act
        $money = Money::of(12_99, $currency);

        // Assert
        self::assertSame(1299, $money->amount);
        self::assertTrue($money->currency->equals($currency));
    }

    #[Test]
    public function it_creates_zero(): void
    {
        // Arrange
        $currency = Currency::of('PLN');

        // Act
        $money = Money::zero($currency);

        // Assert
        self::assertTrue($money->isZero());
    }

    #[Test]
    public function it_adds_money_in_the_same_currency(): void
    {
        // Arrange
        $a = self::pln(1000);
        $b = self::pln(250);

        // Act
        $sum = $a->add($b);

        // Assert
        self::assertTrue($sum->equals(self::pln(1250)));
    }

    #[Test]
    public function it_subtracts_money_in_the_same_currency(): void
    {
        // Arrange
        $a = self::pln(1000);
        $b = self::pln(1250);

        // Act
        $difference = $a->subtract($b);

        // Assert
        self::assertTrue($difference->equals(self::pln(-250)));
        self::assertTrue($difference->isNegative());
    }

    #[Test]
    public function it_multiplies_by_a_quantity(): void
    {
        // Arrange
        $unitPrice = self::pln(1299);

        // Act
        $lineTotal = $unitPrice->multiply(3);

        // Assert
        self::assertTrue($lineTotal->equals(self::pln(3897)));
    }

    #[Test]
    public function it_does_not_mutate_the_original_instance(): void
    {
        // Arrange
        $original = self::pln(1000);

        // Act
        $original->add(self::pln(1));

        // Assert
        self::assertSame(1000, $original->amount);
    }

    #[Test]
    #[DataProvider('comparisons')]
    public function it_compares_amounts(int $a, int $b, bool $greater, bool $less, bool $equal): void
    {
        // Arrange
        $left = self::pln($a);
        $right = self::pln($b);

        // Act
        $isGreater = $left->isGreaterThan($right);
        $isLess = $left->isLessThan($right);
        $isEqual = $left->equals($right);

        // Assert
        self::assertSame($greater, $isGreater);
        self::assertSame($less, $isLess);
        self::assertSame($equal, $isEqual);
    }

    /**
     * @return iterable<string, array{int, int, bool, bool, bool}>
     */
    public static function comparisons(): iterable
    {
        yield 'greater' => [200, 100, true, false, false];
        yield 'less' => [100, 200, false, true, false];
        yield 'equal' => [100, 100, false, false, true];
    }

    #[Test]
    public function it_does_not_equal_the_same_amount_in_another_currency(): void
    {
        // Arrange
        $pln = self::pln(100);
        $eur = Money::of(100, Currency::of('EUR'));

        // Act
        $equals = $pln->equals($eur);

        // Assert
        self::assertFalse($equals);
    }

    /**
     * @param \Closure(Money, Money): mixed $operation
     */
    #[Test]
    #[DataProvider('currencySensitiveOperations')]
    public function it_refuses_to_mix_currencies(\Closure $operation): void
    {
        // Arrange
        $pln = self::pln(100);
        $eur = Money::of(100, Currency::of('EUR'));

        // Assert
        $this->expectException(CurrencyMismatch::class);
        $this->expectExceptionMessage('Expected money in PLN, got EUR.');

        // Act
        $operation($pln, $eur);
    }

    /**
     * @return iterable<string, array{\Closure(Money, Money): mixed}>
     */
    public static function currencySensitiveOperations(): iterable
    {
        yield 'add' => [static fn(Money $a, Money $b): Money => $a->add($b)];
        yield 'subtract' => [static fn(Money $a, Money $b): Money => $a->subtract($b)];
        yield 'greater than' => [static fn(Money $a, Money $b): bool => $a->isGreaterThan($b)];
        yield 'less than' => [static fn(Money $a, Money $b): bool => $a->isLessThan($b)];
    }

    private static function pln(int $amount): Money
    {
        return Money::of($amount, Currency::of('PLN'));
    }
}
