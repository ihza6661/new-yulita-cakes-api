<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['payment', 'shipment']);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => (float) $this->total_amount,
            'shipping_cost' => (float) $this->shipping_cost,
            'status' => $this->status->value,
            'status_label' => $this->status->name,
            'order_date' => $this->created_at,

            'payment_status' => optional($this->payment)->status?->value ?? 'N/A',
            'payment_status_label' => optional($this->payment)->status?->name ?? 'N/A',

            'shipment_status' => optional($this->shipment)->status?->value ?? 'N/A',
            'shipment_status_label' => optional($this->shipment)->status?->name ?? 'N/A',

            'user' => new SiteUserResource($this->whenLoaded('user')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),

        ];
    }
}
