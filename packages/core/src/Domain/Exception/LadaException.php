<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Exception;

/**
 * Marker for every exception thrown by Lada, so integrators can catch them in one place.
 */
interface LadaException extends \Throwable {}
