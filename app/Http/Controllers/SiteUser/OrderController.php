<?php

namespace App\Http\Controllers\SiteUser;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SiteUser\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with([
                'orderItems:id,order_id,qty,price',
                'payment:id,order_id,status',
                'shipment:id,order_id,status'
            ])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource|JsonResponse
    {
        $order->loadMissing([
            'orderItems.product' => function ($query) {
                $query->with(['images' => function ($imgQuery) {
                    $imgQuery->where('is_primary', true)->orWhere(fn($q) => $q->limit(1));
                }]);
            },
            'address',
            'payment',
            'shipment'
        ]);

        return new OrderResource($order);
    }

    public function confirmDelivery(Request $request, Order $order): JsonResponse|OrderResource
    {
        if ($order->status !== OrderStatus::SHIPPED) {
            return response()->json([
                'message' => 'Pesanan ini tidak dalam status "Dikirim" sehingga tidak bisa dikonfirmasi penerimaannya.',
                'current_status' => $order->status->value
            ], 409);
        }

        $order->status = OrderStatus::DELIVERED;
        $order->save();

        if ($order->shipment) {
            $order->shipment->update(['status' => ShipmentStatus::DELIVERED ?? 'delivered']);
        }

        $order->loadMissing(['payment', 'shipment', 'address', 'orderItems.product.images']);

        return response()->json([
            'message' => 'Konfirmasi penerimaan pesanan berhasil.',
            'order' => new OrderResource($order)
        ], 200);
    }
}
