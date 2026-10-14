<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Order;

use Lada\Core\Domain\Cart\CartLine;
use Lada\Core\Domain\Cart\Totals;
use Lada\Core\Domain\Shared\Url;

/**
 * State of an order in the shop, as shown to the agent and sent in webhooks.
 */
final readonly class OrderSnapshot
{
    /**
     * @param list<CartLine> $lines ordered lines with the prices the buyer paid
     * @param Url|null       $url   order status page in the shop
     */
    public function __construct(
        public OrderRef $ref,
        public OrderStatus $status,
        public array $lines,
        public Totals $totals,
        public \DateTimeImmutable $placedAt,
        public ?Url $url = null,
    ) {}
}
