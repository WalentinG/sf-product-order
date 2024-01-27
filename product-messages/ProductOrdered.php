<?php

declare(strict_types=1);

namespace Product\Messages;

final readonly class ProductOrdered
{
    public function __construct(public string $productId, public string $orderId)
    {
    }
}
