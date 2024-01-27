<?php

declare(strict_types=1);

namespace App\tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Product\Messages\OrderProduct;
use Product\Messages\OrderProductFailed;
use Product\Messages\ProductAdded;
use Product\Messages\ProductOrdered;
use Product\Product;
use Support\Testing\OpenApi;
use Support\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class ProductTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function testGetProducts(): void
    {
        $client = ProductTest::createClient();
        $this->makeProduct('550e8400-e29b-41d4-a716-446655440000', 10);
        $this->makeProduct('550e8400-e29b-41d4-a716-446655440001', 10);

        $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
    }

    public function testGetProduct(): void
    {
        $client = ProductTest::createClient();
        self::makeProduct($id = '550e8400-e29b-41d4-a716-446655440000', 10);

        $client->request('GET', "/products/{$id}");

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
    }

    public function testCreateProduct(): void
    {
        $client = ProductTest::createClient();

        $client->request('POST', '/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data = [
            'name' => 'Coffee Mug',
            'price' => 12.99,
            'quantity' => 100,
        ]));

        $this->assertResponseIsSuccessful();
        OpenApi::assertEndpoint($client->getRequest(), $client->getResponse());
        $this->assertProductData(json_decode($client->getResponse()->getContent(), true)['id'], $data);
        $this->transport('product-event')->queue()->assertCount(1);
        $this->transport('product-event')->queue()->assertContains(ProductAdded::class);
    }

    public function testOrderProductSuccessfully(): void
    {
        $this->makeProduct($id = '550e8400-e29b-41d4-a716-446655440000', 5);

        $this->transport('product-cmd')->send(new OrderProduct($id, Uuid::v4()->value, 2));
        $this->transport('product-cmd')->process(1);

        $this->transport('product-event')->queue()->assertCount(1);
        $this->transport('product-event')->queue()->assertContains(ProductOrdered::class);
        $this->assertProductData($id, ['quantity' => 3]);
    }

    public function testOrderProductFailed(): void
    {
        $this->makeProduct($id = '550e8400-e29b-41d4-a716-446655440000', 5);

        $this->transport('product-cmd')->send(new OrderProduct($id, Uuid::v4()->value, 10));
        $this->transport('product-cmd')->process(1);

        $this->transport('product-event')->queue()->assertCount(1);
        $this->transport('product-event')->queue()->assertContains(OrderProductFailed::class);
        $this->assertProductData($id, ['quantity' => 5]);
    }

    private function makeProduct(string $uuid, int $quantity): void
    {
        $this->getContainer()->get(Connection::class)->insert('products', [
            'id' => $uuid,
            'name' => 'Coffee Mug',
            'price' => 12.99,
            'quantity' => $quantity,
        ]);
    }

    private function assertProductData(string $id, array $data): void
    {
        $query = 'SELECT '.implode(', ', array_keys($data)).' FROM products WHERE id = :id';

        $this->assertEquals($data, $this->getContainer()->get(Connection::class)->fetchAssociative($query, ['id' => $id]));
    }
}
