<?php

declare(strict_types=1);

namespace Lada\Core\Application\Port;

use Lada\Core\Domain\Cart\CartSnapshot;
use Lada\Core\Domain\Payment\GatewayPayment;
use Lada\Core\Domain\Payment\PaymentInstrument;
use Lada\Core\Domain\Shared\Channel;

/**
 * Implemented by the shop, one per accepted agent payment method (e.g. Google Pay, ACP Shared Payment Token).
 * A handler never calls a PSP: it only translates the agent's credential into the shop's payment data.
 */
interface PaymentHandler
{
    /**
     * Handler id as used by the protocols, e.g. "com.google.pay".
     */
    public function id(): string;

    /**
     * Handler configuration advertised to agents in discovery (e.g. merchant id, allowed card networks).
     *
     * @return array<string, mixed>
     */
    public function describe(Channel $channel): array;

    public function toGatewayPayment(PaymentInstrument $instrument, CartSnapshot $cart): GatewayPayment;
}
