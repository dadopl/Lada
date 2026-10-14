<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Catalog;

use Lada\Core\Domain\Exception\CurrencyMismatch;
use Lada\Core\Domain\Exception\InvalidCatalog;
use Lada\Core\Domain\Shared\Money;
use Lada\Core\Domain\Shared\Url;

/**
 * A purchasable product (or variant) priced by the shop for a given PricingContext.
 */
final readonly class Product
{
    /**
     * @param Money|null $listPrice regular price before a promotion, shown as "was …"
     * @param list<Url>  $images
     */
    public function __construct(
        public ProductRef $ref,
        public string $title,
        public Money $price,
        public Availability $availability,
        public Url $url,
        public ?string $description = null,
        public ?Money $listPrice = null,
        public array $images = [],
    ) {
        if ('' === trim($title)) {
            throw InvalidCatalog::emptyText('Product title');
        }

        if (null !== $listPrice && !$listPrice->currency->equals($price->currency)) {
            throw CurrencyMismatch::between($price->currency, $listPrice->currency);
        }
    }

    public function isPurchasable(): bool
    {
        return Availability::OutOfStock !== $this->availability;
    }
}
