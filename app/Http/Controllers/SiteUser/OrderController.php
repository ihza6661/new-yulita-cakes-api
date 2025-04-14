<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteUser\OrderResource; // Gunakan OrderResource
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Hanya untuk error jika perlu
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
}
