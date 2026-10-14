<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Buyer;

/**
 * The person buying through the agent. Agents collect data gradually, so every field is optional;
 * what the shop still needs is reported in CartSnapshot::$missing.
 */
final readonly class Buyer
{
    public function __construct(
        public ?Email $email = null,
        public ?string $fullName = null,
        public ?string $phone = null,
    ) {}
}
