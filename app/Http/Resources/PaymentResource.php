<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing('order.user');

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_type' => $this->payment_type,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status->value,
            'status_label' => $this->status->name,
            'amount' => (float) $this->amount,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
