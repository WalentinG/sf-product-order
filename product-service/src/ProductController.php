<?php

declare(strict_types=1);

namespace Product;

use Support\Http\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Autoconfigure(autowire: true), Route('/products')]
final class ProductController extends AbstractController
{
    #[Route('', name: 'getProducts', methods: ['GET'])]
    public function list(Products $products): JsonResponse
    {
        return $this->toJson($products->findAll());
    }

    #[Route('', name: 'createProduct', methods: ['POST'])]
    public function create(#[MapRequestPayload] AddProduct $cmd, ProductService $productService): Response
    {
        return $this->toJson($productService->add($cmd), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'getProduct', methods: ['GET'])]
    public function get(Product $product): JsonResponse
    {
        return $this->json($product);
    }
}
