<?php

namespace App\Http\Controllers\SiteUser;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteUser\StoreReviewRequest;
use App\Http\Requests\SiteUser\UpdateReviewRequest;
use App\Http\Resources\SiteUser\ReviewResource;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductReviewController extends Controller
{
    public function index(Request $request, $productId): AnonymousResourceCollection|JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        $perPage = $request->input('per_page', 5);
        $reviews = ProductReview::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request, $productId): JsonResponse|ReviewResource
    {
        $user = $request->user();
        $validated = $request->validated();

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        $hasPurchasedAndDelivered = Order::where('site_user_id', $user->id)
            ->where('status', OrderStatus::DELIVERED)
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if (!$hasPurchasedAndDelivered) {
            return response()->json(['message' => 'Anda hanya bisa mereview produk yang sudah dibeli dan diterima.'], 403);
        }

        $existingReview = ProductReview::where('site_user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah memberikan review untuk produk ini.'], 409);
        }

        $review = ProductReview::create([
            'site_user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        $review->load('user');

        return response()->json([
            'message' => 'Review berhasil ditambahkan.',
            'review' => new ReviewResource($review)
        ], 201);
    }

    public function update(UpdateReviewRequest $request, ProductReview $review): JsonResponse|ReviewResource
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($review->site_user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah review ini.'], 403);
        }

        $review->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? $review->review,
        ]);

        $review->load('user');

        return response()->json([
            'message' => 'Review berhasil diperbarui.',
            'review' => new ReviewResource($review->fresh())
        ], 200);
    }

    public function destroy(Request $request, ProductReview $review): JsonResponse
    {
        $user = $request->user();

        if ($review->site_user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus review ini.'], 403);
        }

        $review->delete();

        return response()->json(null, 204);
    }

    public function eligibility(Request $request, $productId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['can_review' => false, 'has_reviewed' => false, 'reason' => 'unauthenticated']);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['can_review' => false, 'has_reviewed' => false, 'reason' => 'product_not_found']);
        }

        $hasReviewed = ProductReview::where('site_user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($hasReviewed) {
            return response()->json(['can_review' => false, 'has_reviewed' => true, 'reason' => 'already_reviewed']);
        }

        $hasPurchasedAndDelivered = Order::where('site_user_id', $user->id)
            ->where('status', OrderStatus::DELIVERED)
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if ($hasPurchasedAndDelivered) {
            return response()->json(['can_review' => true, 'has_reviewed' => false, 'reason' => null]);
        } else {
            $hasPurchased = Order::where('site_user_id', $user->id)
                ->whereHas('orderItems', fn($q) => $q->where('product_id', $productId))
                ->exists();
            $reason = !$hasPurchased ? 'not_purchased' : 'not_delivered';
            return response()->json(['can_review' => false, 'has_reviewed' => false, 'reason' => $reason]);
        }
    }

    public function getFeaturedReviews(Request $request): AnonymousResourceCollection
    {
        $limit = $request->input('limit', 3);

        $featuredReviews = ProductReview::with('user')
            ->where('rating', '>=', 3)
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->latest()
            ->take($limit)
            ->get();

        return ReviewResource::collection($featuredReviews);
    }
}
