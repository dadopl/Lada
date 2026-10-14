<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Order;

enum PlacementOutcome: string
{
    /** The order exists and the gateway accepted the payment. */
    case Placed = 'placed';

    /** The buyer has to finish on the shop's or PSP's page (3-D Secure, hosted payment page). */
    case RequiresAction = 'requires_action';

    /** The gateway declined the payment; no order was created. */
    case Declined = 'declined';

    /** The total differs from the one the agent accepted; nothing was charged. */
    case TotalChanged = 'total_changed';

    /** Some lines can no longer be bought; nothing was charged. */
    case OutOfStock = 'out_of_stock';
}
