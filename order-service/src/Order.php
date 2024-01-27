<?php

declare(strict_types=1);

namespace Order;

use Doctrine\ORM\Mapping as ORM;
use Support\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public readonly string $orderId,
        #[ORM\ManyToOne(targetEntity: Product::class, fetch: 'EAGER')]
        #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
        public readonly Product $product,
        #[ORM\Column(length: 255)]
        public readonly int $quantityOrdered,
        #[ORM\Column(length: 255)]
        public readonly string $customerName,
        #[ORM\Column]
        public string $orderStatus,
    ) {
    }

    public static function placeOrder(Product $product, int $quantity, string $customerName): self
    {
        return new Order(
            orderId: Uuid::v4()->value,
            product: $product,
            quantityOrdered: $quantity,
            customerName: $customerName,
            orderStatus: OrderStatus::processing->value
        );
    }

    public function complete(): void
    {
        if ($this->orderStatus === OrderStatus::failed->value) {
            throw new \LogicException('Cannot complete a cancelled order-service.');
        }

        $this->orderStatus = OrderStatus::completed->value;
    }

    public function fail(): void
    {
        if ($this->orderStatus === OrderStatus::completed->value) {
            throw new \LogicException('Cannot cancel a completed order-service.');
        }

        $this->orderStatus = OrderStatus::failed->value;
    }
}
