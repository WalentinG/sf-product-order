<?php

declare(strict_types=1);

namespace Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Product\Messages\OrderProduct;
use Product\Messages\OrderProductFailed;
use Product\Messages\ProductAdded;
use Product\Messages\ProductOrdered;
use Support\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[Autoconfigure(autowire: true)]
final readonly class ProductService
{
    public function __construct(private EntityManagerInterface $em, private MessageBusInterface $bus)
    {
    }

    public function add(AddProduct $cmd): Product
    {
        $product = new Product(Uuid::v4()->value, $cmd->name, $cmd->price, $cmd->quantity);

        $this->em->persist($product);
        $this->em->flush();

        $this->bus->dispatch(new ProductAdded($product->id, $product->name, $product->price));

        return $product;
    }

    #[AsMessageHandler]
    public function order(OrderProduct $cmd): void
    {
        try {
            $product = $this->em->find(Product::class, $cmd->productId)
                ?? throw EntityNotFoundException::fromClassNameAndIdentifier(Product::class, [$cmd->productId]);

            $product->orderProduct($cmd->quantity);

            $this->em->flush();

            $this->bus->dispatch(new ProductOrdered($cmd->productId, $cmd->orderId));
        } catch (\DomainException) {
            $this->bus->dispatch(new OrderProductFailed($cmd->productId, $cmd->orderId));
        }
    }
}
