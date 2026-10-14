<?php

declare(strict_types=1);

namespace Lada\Core\Application\Port;

use Lada\Core\Domain\Exception\OrderNotFound;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Order\OrderSnapshot;

/**
 * Implemented by the shop: current state of an order.
 */
interface OrderPort
{
    /**
     * @throws OrderNotFound
     */
    public function get(OrderRef $ref): OrderSnapshot;
}
