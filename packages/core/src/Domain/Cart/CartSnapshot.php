<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCart;
use Lada\Core\Domain\Shared\Money;

/**
 * State of the shop's cart at a point in time. The only source of prices in Lada.
 */
final readonly class CartSnapshot
{
    /**
     * @param list<CartLine>          $lines
     * @param list<FulfillmentOption> $fulfillmentOptions
     * @param list<Message>           $messages
     * @param list<Requirement>       $missing            data the shop still needs before placing the order
     */
    public function __construct(
        public CartRef $ref,
        public array $lines,
        public Totals $totals,
        public array $fulfillmentOptions = [],
        public ?string $selectedFulfillmentOptionId = null,
        public array $messages = [],
        public array $missing = [],
    ) {
        self::assertUnique('Cart line', array_map(static fn(CartLine $line): string => $line->ref->value, $lines));
        self::assertUnique('Fulfillment option', array_map(static fn(FulfillmentOption $option): string => $option->id, $fulfillmentOptions));
        $this->assertSameCurrency();
        $this->assertSelectedFulfillmentOptionIsOffered();
    }

    public function selectedFulfillmentOption(): ?FulfillmentOption
    {
        foreach ($this->fulfillmentOptions as $option) {
            if ($option->id === $this->selectedFulfillmentOptionId) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @param list<string> $ids
     */
    private static function assertUnique(string $what, array $ids): void
    {
        $seen = [];

        foreach ($ids as $id) {
            if (isset($seen[$id])) {
                throw InvalidCart::duplicate($what, $id);
            }

            $seen[$id] = true;
        }
    }

    private function assertSameCurrency(): void
    {
        $amounts = [
            ...array_map(static fn(CartLine $line): Money => $line->total, $this->lines),
            ...array_map(static fn(FulfillmentOption $option): Money => $option->price, $this->fulfillmentOptions),
        ];

        foreach ($amounts as $amount) {
            if (!$amount->currency->equals($this->totals->currency)) {
                throw CurrencyMismatch::between($this->totals->currency, $amount->currency);
            }
        }
    }

    private function assertSelectedFulfillmentOptionIsOffered(): void
    {
        if (null !== $this->selectedFulfillmentOptionId && null === $this->selectedFulfillmentOption()) {
            throw InvalidCart::unknownFulfillmentOption($this->selectedFulfillmentOptionId);
        }
    }
}
