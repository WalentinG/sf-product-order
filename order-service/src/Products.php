<?php

declare(strict_types=1);

namespace Order;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/** @template-extends ServiceEntityRepository<Product> */
#[Autoconfigure(autowire: true)]
final class Products extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function get(string $productId): Product
    {
        return $this->_em->find(Product::class, $productId)
            ?? throw EntityNotFoundException::fromClassNameAndIdentifier(Product::class, [$productId]);
    }
}
