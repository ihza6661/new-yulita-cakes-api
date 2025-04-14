<?php

namespace App\Http\Resources\SiteUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $grandTotal = $this->total_amount + $this->shipping_cost;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'shipping_cost' => $this->shipping_cost,
            'grand_total' => $grandTotal,
            'status' => $this->status,
            'order_date' => $this->created_at->format('Y-m-d H:i:s'),
            'address' => new AddressResource($this->whenLoaded('address')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
