<?php

declare(strict_types=1);

namespace Order;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'products')]
readonly class Product
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public string $id,
        #[ORM\Column]
        public string $name,
        #[ORM\Column]
        public float $price,
    ) {
    }
}
