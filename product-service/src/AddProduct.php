<?php

declare(strict_types=1);

namespace Product;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Validator\Constraints as Assert;

#[Autoconfigure(autowire: false)]
final readonly class AddProduct
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public string $name,
        #[Assert\PositiveOrZero]
        public float $price,
        #[Assert\PositiveOrZero]
        public int $quantity,
    ) {
    }
}
