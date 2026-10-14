<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

/**
 * Data the shop still needs before the cart can be turned into an order.
 */
enum Requirement: string
{
    case BuyerEmail = 'buyer_email';
    case ShippingAddress = 'shipping_address';
    case BillingAddress = 'billing_address';
    case FulfillmentOption = 'fulfillment_option';
    case Payment = 'payment';
}
