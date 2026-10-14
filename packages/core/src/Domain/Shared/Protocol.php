<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Shared;

/**
 * Agent-commerce protocol a request came through.
 */
enum Protocol: string
{
    case Ucp = 'ucp';
    case Acp = 'acp';
    case Mcp = 'mcp';
}
