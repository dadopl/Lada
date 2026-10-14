<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Checkout;

/**
 * Status of a checkout session in the core. Protocol bindings map it onto UCP's and ACP's own statuses.
 */
enum CheckoutStatus: string
{
    /** Data is missing or a problem the agent can fix blocks the purchase. */
    case Incomplete = 'incomplete';

    /** The buyer has to provide data or review something the agent cannot handle. */
    case RequiresEscalation = 'requires_escalation';

    /** Everything is in place; the agent may complete with a payment. */
    case ReadyForComplete = 'ready_for_complete';

    /** The order is being placed; no changes are allowed. */
    case CompleteInProgress = 'complete_in_progress';

    case Completed = 'completed';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Canceled, self::Expired => true,
            self::Incomplete, self::RequiresEscalation, self::ReadyForComplete, self::CompleteInProgress => false,
        };
    }
}
