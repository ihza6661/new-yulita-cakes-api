<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'category_id' => $this->category_id,
            'original_price' => (float) $this->original_price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'stock' => (int) $this->stock,
            'weight' => (float) $this->weight,
            'description' => $this->description,
            'label' => $this->label,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}