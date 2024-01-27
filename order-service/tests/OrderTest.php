<?php

declare(strict_types=1);

namespace App\tests;

use Doctrine\DBAL\Connection;
use Order\OrderStatus;
use Product\Messages\OrderProduct;
use Product\Messages\OrderProductFailed;
use Product\Messages\ProductAdded;
use Product\Messages\ProductOrdered;
use Support\Str;
use Support\Testing\OpenApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class OrderTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function testGetProducts(): void
    {
        $client = OrderTest::createClient();
        $this->makeProduct($id = '550e8400-e29b-41d4-a716-446655440000');
        $this->makeOrder('550e8400-e29b-41d4-a716-446655440001', $id, 10);

        $client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
    }

    public function testGetProduct(): void
    {
        $client = OrderTest::createClient();
        $this->makeProduct($productId = '550e8400-e29b-41d4-a716-446655440000');
        $this->makeOrder($orderId = '550e8400-e29b-41d4-a716-446655440001', $productId, 10);

        $client->request('GET', "/orders/{$orderId}");

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
    }

        public function testCreateProduct(): void
    {
        $client = OrderTest::createClient();
        $this->makeProduct($productId = '550e8400-e29b-41d4-a716-446655440000');

        $client->request('POST', '/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data = [
            'customerName' => 'John Doe',
            'productId' => $productId,
            'quantityOrdered' => 5,
        ]));

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
        $this->assertOrderData(json_decode($client->getResponse()->getContent(), true)['orderId'], $data);
        $this->transport('product-cmd')->queue()->assertCount(1);
        $this->transport('product-cmd')->queue()->assertContains(OrderProduct::class);
    }

    public function testWhenProductAdded(): void
    {
        $this->transport('product-event')->send(new ProductAdded(
            id: $productId = '550e8400-e29b-41d4-a716-446655440000',
            name: 'Coffee Mug',
            price: $price = 12.99
        ));
        $this->transport('product-event')->process(1);

        $this->assertProductData($productId, ['price' => $price]);
    }

    public function testWhenProductOrdered(): void
    {
        $this->makeProduct($productId = '550e8400-e29b-41d4-a716-446655440000');
        $this->makeOrder($orderId = '550e8400-e29b-41d4-a716-446655440001', $productId, 10);

        $this->transport('product-event')->send(new ProductOrdered($productId, $orderId));
        $this->transport('product-event')->process(1);

        $this->assertOrderData($orderId, ['orderStatus' => OrderStatus::completed->value]);
    }

    public function testWhenOrderProductFailed(): void
    {
        $this->makeProduct($productId = '550e8400-e29b-41d4-a716-446655440000');
        $this->makeOrder($orderId = '550e8400-e29b-41d4-a716-446655440001', $productId, 10);

        $this->transport('product-event')->send(new OrderProductFailed($productId, $orderId));
        $this->transport('product-event')->process(1);

        $this->assertOrderData($orderId, ['orderStatus' => OrderStatus::failed->value]);
    }

    private function makeProduct(string $uuid): void
    {
        $this->getContainer()->get(Connection::class)->insert('products', [
            'id' => $uuid,
            'name' => 'Coffee Mug',
            'price' => 12.99,
        ]);
    }

    private function makeOrder(string $uuid, string $productId, int $quantityOrdered): void
    {
        $this->getContainer()->get(Connection::class)->insert('orders', [
            'order_id' => $uuid,
            'product_id' => $productId,
            'customer_name' => 'John Doe',
            'quantity_ordered' => $quantityOrdered,
            'order_status' => OrderStatus::processing->value,
        ]);
    }

    private function assertProductData(string $id, array $data): void
    {
        $query = 'SELECT '.implode(', ', array_keys($data)).' FROM products WHERE id = :id';

        $this->assertEquals($data, $this->getContainer()->get(Connection::class)->fetchAssociative($query, ['id' => $id]));
    }

    private function assertOrderData(string $id, array $data): void
    {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data[Str::camelCaseToSnakeCase($key)] = $value;
        }

        $query = 'SELECT '.implode(', ', array_keys($data)).' FROM orders WHERE order_id = :id';

        $this->assertEquals($data, $this->getContainer()->get(Connection::class)->fetchAssociative($query, ['id' => $id]));
    }
}
