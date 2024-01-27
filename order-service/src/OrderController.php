<?php

declare(strict_types=1);

namespace Order;

use Support\Http\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Autoconfigure(autowire: true), Route('/orders')]
final class OrderController extends AbstractController
{
    #[Route('', name: 'getOrders', methods: ['GET'])]
    public function list(Orders $orders): JsonResponse
    {
        return $this->toJson($orders->findAll());
    }

    #[Route('', name: 'createOrder', methods: ['POST'])]
    public function create(#[MapRequestPayload] PlaceOrder $placeOrder, OrderService $orderService): JsonResponse
    {
        return $this->toJson($orderService->placeOrder($placeOrder),Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'getOrder', methods: ['GET'])]
    public function get(Order $order): JsonResponse
    {
        return $this->toJson($order);
    }
}
