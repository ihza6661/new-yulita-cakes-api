<?php

namespace App\Http\Resources\SiteUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'review_date' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
