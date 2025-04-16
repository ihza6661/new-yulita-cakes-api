<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing('product');

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->product_name ?? 'Produk Dihapus',
            'qty' => (int) $this->qty,
            'price' => (float) $this->price,
        ];
    }
}
