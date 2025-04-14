<?php

namespace App\Http\Resources\SiteUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
