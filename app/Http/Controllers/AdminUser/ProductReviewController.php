<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\ProductReview;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductReviewController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $reviews = ProductReview::with([
            'user:id,name',
            'product:id,product_name'
        ])
            ->latest()
            ->get();

        return ProductReviewResource::collection($reviews);
    }

    public function show(ProductReview $productReview): ProductReviewResource
    {
        $productReview->load(['user:id,name,email', 'product:id,product_name']);

        return new ProductReviewResource($productReview);
    }
}
