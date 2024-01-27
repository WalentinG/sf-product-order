<?php

declare(strict_types=1);

namespace Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Product\Messages\OrderProduct;
use Product\Messages\OrderProductFailed;
use Product\Messages\ProductAdded;
use Product\Messages\ProductOrdered;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[Autoconfigure(autowire: true)]
final readonly class OrderService
{
    public function __construct(
        private Orders $orders,
        private Products $products,
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {
    }

    public function placeOrder(PlaceOrder $cmd): Order
    {
        $product = $this->products->get($cmd->productId);
        $order = Order::placeOrder($product, $cmd->quantityOrdered, $cmd->customerName);

        $this->em->persist($order);
        $this->em->flush();

        $this->bus->dispatch(new OrderProduct($order->product->id, $order->orderId, $order->quantityOrdered));

        return $order;
    }

    #[AsMessageHandler]
    public function whenProductOrdered(ProductOrdered $event): void
    {
        $this->orders->get($event->orderId)->complete();

        $this->em->flush();
    }

    #[AsMessageHandler]
    public function whenOrderProductFailed(OrderProductFailed $event): void
    {
        $this->orders->get($event->orderId)->fail();

        $this->em->flush();
    }

    #[AsMessageHandler]
    public function whenProductAdded(ProductAdded $event): void
    {
        $this->em->persist(new Product($event->id, $event->name, $event->price));

        $this->em->flush();
    }
}
