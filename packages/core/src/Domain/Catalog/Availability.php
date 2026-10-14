<?php

declare(strict_types=1);

namespace Lada\Core\Domain\Catalog;

enum Availability: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case BackOrder = 'backorder';
    case PreOrder = 'preorder';
}
