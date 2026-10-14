<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

use Lada\Core\Domain\Exception\CurrencyMismatch;

/**
 * An amount in minor units (e.g. grosze, cents) of a single currency.
 *
 * Lada never calculates prices; Money only carries amounts reported by the shop
 * and compares them (e.g. the total the agent accepted against the current one).
 */
final readonly class Money
{
    private function __construct(
        public int $amount,
        public Currency $currency,
    ) {}

    public static function of(int $amount, Currency $currency): self
    {
        return new self($amount, $currency);
    }

    public static function zero(Currency $currency): self
    {
        return new self(0, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->currency->equals($other->currency) && $this->amount === $other->amount;
    }

    public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function isLessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amount < $other->amount;
    }

    public function isZero(): bool
    {
        return 0 === $this->amount;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    private function assertSameCurrency(self $other): void
    {
        if (!$this->currency->equals($other->currency)) {
            throw CurrencyMismatch::between($this->currency, $other->currency);
        }
    }
}
