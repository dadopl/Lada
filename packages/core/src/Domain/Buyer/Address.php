<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Buyer;

use Lada\Core\Domain\Exception\InvalidBuyer;
use Lada\Core\Domain\Shared\Country;

/**
 * Postal address for shipping or billing.
 */
final readonly class Address
{
    public function __construct(
        public string $name,
        public string $street,
        public string $city,
        public string $postalCode,
        public Country $country,
        public ?string $streetLine2 = null,
        public ?string $region = null,
        public ?string $phone = null,
    ) {
        foreach (['Address name' => $name, 'Street' => $street, 'City' => $city, 'Postal code' => $postalCode] as $field => $value) {
            if ('' === trim($value)) {
                throw InvalidBuyer::emptyText($field);
            }
        }
    }
}
