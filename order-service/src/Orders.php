<?php

declare(strict_types=1);

namespace Order;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/** @template-extends ServiceEntityRepository<Order> */
#[Autoconfigure(autowire: true)]
final class Orders extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function get(string $orderId): Order
    {
        return $this->_em->find(Order::class, $orderId)
            ?? throw EntityNotFoundException::fromClassNameAndIdentifier(Order::class, [$orderId]);
    }
}
