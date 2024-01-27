<?php

declare(strict_types=1);

namespace Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        #[Assert\Uuid]
        public readonly string $id,
        #[ORM\Column(length: 255)]
        #[Assert\Length(min: 2, max: 255)]
        public readonly string $name,
        #[ORM\Column]
        #[Assert\PositiveOrZero]
        public readonly float $price,
        #[ORM\Column]
        #[Assert\PositiveOrZero]
        public int $quantity,
    ) {
    }

    public function orderProduct(int $quantity): void
    {
        if ($this->quantity < $quantity) {
            throw new \DomainException('Not enough quantity');
        }
        $this->quantity -= $quantity;
    }
}
