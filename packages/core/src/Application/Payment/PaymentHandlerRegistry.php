<?php

declare(strict_types=1);

namespace Lada\Core\Application\Payment;

use Lada\Core\Application\Exception\DuplicatePaymentHandler;
use Lada\Core\Application\Exception\UnknownPaymentHandler;
use Lada\Core\Application\Port\PaymentHandler;
use Lada\Core\Domain\Payment\PaymentInstrument;

/**
 * The payment handlers the shop accepts, by id.
 */
final readonly class PaymentHandlerRegistry
{
    /** @var array<string, PaymentHandler> */
    private array $handlers;

    /**
     * @param iterable<PaymentHandler> $handlers
     */
    public function __construct(iterable $handlers)
    {
        $byId = [];

        foreach ($handlers as $handler) {
            if (isset($byId[$handler->id()])) {
                throw DuplicatePaymentHandler::withId($handler->id());
            }

            $byId[$handler->id()] = $handler;
        }

        $this->handlers = $byId;
    }

    /**
     * @throws UnknownPaymentHandler
     */
    public function forInstrument(PaymentInstrument $instrument): PaymentHandler
    {
        return $this->handlers[$instrument->handlerId] ?? throw UnknownPaymentHandler::withId($instrument->handlerId);
    }

    /**
     * @return list<PaymentHandler>
     */
    public function all(): array
    {
        return array_values($this->handlers);
    }
}
