<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Cart;

use Lada\Core\Domain\Exception\InvalidCart;

/**
 * A message from the shop about the cart, e.g. "out of stock" or "address required".
 */
final readonly class Message
{
    /**
     * @param string      $code machine-readable code, e.g. "out_of_stock"
     * @param string      $text human-readable text in the channel locale
     * @param string|null $path JSON path of the field the message is about, e.g. "$.line_items[0]"
     */
    public function __construct(
        public Severity $severity,
        public string $code,
        public string $text,
        public ?string $path = null,
    ) {
        if ('' === $code) {
            throw InvalidCart::emptyText('Message code');
        }
    }
}
