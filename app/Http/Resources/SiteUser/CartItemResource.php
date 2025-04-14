<?php

namespace App\Http\Resources\SiteUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart_item_id' => $this->id,
            'qty' => $this->qty,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
