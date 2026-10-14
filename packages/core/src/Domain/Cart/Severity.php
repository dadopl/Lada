<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

/**
 * How serious a message reported by the shop about the cart is.
 */
enum Severity: string
{
    /** Informational only, e.g. "free shipping above 200 PLN". */
    case Info = 'info';

    /** Worth showing to the buyer, does not block the purchase. */
    case Warning = 'warning';

    /** Blocks the purchase, the agent can fix it on its own (e.g. pick another shipping option). */
    case Recoverable = 'recoverable';

    /** Blocks the purchase until the buyer provides data the agent does not have. */
    case RequiresBuyerInput = 'requires_buyer_input';

    /** Blocks the purchase until the buyer reviews something (e.g. age verification, terms). */
    case RequiresBuyerReview = 'requires_buyer_review';

    public function blocksCompletion(): bool
    {
        return match ($this) {
            self::Info, self::Warning => false,
            self::Recoverable, self::RequiresBuyerInput, self::RequiresBuyerReview => true,
        };
    }

    public function requiresBuyer(): bool
    {
        return match ($this) {
            self::RequiresBuyerInput, self::RequiresBuyerReview => true,
            self::Info, self::Warning, self::Recoverable => false,
        };
    }
}
