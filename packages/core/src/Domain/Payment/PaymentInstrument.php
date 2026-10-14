<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Payment;

use Lada\Core\Domain\Buyer\Address;
use Lada\Core\Domain\Exception\InvalidPayment;

/**
 * A payment credential handed over by the agent, e.g. a Google Pay token or an ACP Shared Payment Token.
 * Lada never charges it; a PaymentHandler translates it into a GatewayPayment for the shop's gateway.
 */
final readonly class PaymentInstrument
{
    /**
     * @param string      $handlerId  payment handler that issued the credential, e.g. "com.google.pay"
     * @param string|null $brand      for display only, e.g. "visa"
     * @param string|null $lastDigits for display only, e.g. "4242"
     */
    public function __construct(
        public string $handlerId,
        #[\SensitiveParameter]
        public string $token,
        public ?string $brand = null,
        public ?string $lastDigits = null,
        public ?Address $billingAddress = null,
    ) {
        if ('' === $handlerId) {
            throw InvalidPayment::emptyText('Payment handler id');
        }

        if ('' === $token) {
            throw InvalidPayment::emptyText('Payment token');
        }
    }

    /**
     * Keeps the credential out of dumps and logs.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'handlerId' => $this->handlerId,
            'token' => '***',
            'brand' => $this->brand,
            'lastDigits' => $this->lastDigits,
            'billingAddress' => $this->billingAddress,
        ];
    }
}
