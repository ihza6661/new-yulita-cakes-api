<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with(['user:id,name', 'payment', 'shipment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        $order->load([
            'user:id,name,email',
            'address',
            'payment',
            'shipment',
            'orderItems.product:id,product_name'
        ]);

        return new OrderResource($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $validatedData = $request->validated();

        $order->update($validatedData);

        $order->load(['user:id,name', 'payment', 'shipment']);

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'order'   => new OrderResource($order)
        ], Response::HTTP_OK);
    }
}
