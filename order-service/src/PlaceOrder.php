<?php

declare(strict_types=1);

namespace Order;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceOrder
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public string $customerName,
        #[Assert\Uuid]
        public string $productId,
        #[Assert\Positive]
        public int $quantityOrdered,
    ) {
    }
}
