<?php

declare(strict_types=1);

namespace Product\Messages;

final readonly class ProductAdded
{
    public function __construct(
        public string $id,
        public string $name,
        public float $price,
    ) {
    }
}
