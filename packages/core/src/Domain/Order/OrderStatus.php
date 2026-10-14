<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Order;

enum OrderStatus: string
{
    case Placed = 'placed';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
    case Refunded = 'refunded';
}
