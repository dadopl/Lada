<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Payment;

use Lada\Core\Domain\Exception\InvalidPayment;

/**
 * Payment in the shop's own terms: one of its payment methods plus the data its gateway expects,
 * e.g. method "stripe" with ["payment_method_data" => ["shared_payment_granted_token" => "spt_…"]].
 */
final readonly class GatewayPayment
{
    /**
     * @param array<string, mixed> $data gateway-specific, may contain credentials
     */
    public function __construct(
        public string $method,
        #[\SensitiveParameter]
        public array $data = [],
    ) {
        if ('' === $method) {
            throw InvalidPayment::emptyText('Payment method');
        }
    }

    /**
     * Keeps gateway credentials out of dumps and logs.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'method' => $this->method,
            'data' => array_fill_keys(array_keys($this->data), '***'),
        ];
    }
}
