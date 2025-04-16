<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['order.user', 'order.payment']);

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'courier' => $this->courier,
            'service' => $this->service,
            'tracking_number' => $this->tracking_number,

            'status' => $this->status->value,
            'status_label' => $this->status->name,

            'payment_status' => optional($this->order?->payment)->status?->value ?? 'N/A',
            'payment_status_label' => optional($this->order?->payment)->status?->name ?? 'N/A',


            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
