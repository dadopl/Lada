<?php

declare(strict_types=1);

namespace Lada\Core\Application\Port;

use Lada\Core\Domain\Buyer\Buyer;
use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Exception\CartNotFound;
use Lada\Core\Domain\Order\PlacementResult;
use Lada\Core\Domain\Payment\GatewayPayment;
use Lada\Core\Domain\Shared\IdempotencyKey;

/**
 * Implemented by the shop: places the order through its regular checkout path, where its gateway charges.
 */
interface OrderPlacementPort
{
    /**
     * The idempotency key should be forwarded to the payment gateway, so a retried call never charges twice.
     *
     * @throws CartNotFound
     */
    public function place(CartRef $ref, GatewayPayment $payment, Buyer $buyer, IdempotencyKey $key): PlacementResult;
}
