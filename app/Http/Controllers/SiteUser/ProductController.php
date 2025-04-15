<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteUser\CategoryResource;
use App\Http\Resources\SiteUser\ProductResource;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function getAllCategories(): AnonymousResourceCollection
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }

    public function getAllProducts(Request $request): AnonymousResourceCollection
    {
        $query = Product::with(['images', 'category']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        $query->orderByRaw('stock > 0 DESC');

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['created_at', 'updated_at', 'product_name', 'original_price', 'stock'];
        if (in_array($sortBy, $allowedSorts) && in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $query->orderBy('id', 'desc');

        $perPage = $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function getLatestProducts(): AnonymousResourceCollection
    {
        $products = Product::with(['images', 'category'])
            ->where('stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        return ProductResource::collection($products);
    }

    public function getProductDetail(Product $product): ProductResource|JsonResponse
    {
        $product->loadMissing('images', 'category');

        return new ProductResource($product);
    }

    public function getRelatedProducts(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'product_id' => 'sometimes|integer|exists:products,id'
        ]);

        $categoryId = $validated['category_id'];
        $productId = $validated['product_id'] ?? null;

        $query = Product::where('category_id', $categoryId)
            ->where('stock', '>', 0);

        if ($productId) {
            $query->where('id', '<>', $productId);
        }

        $relatedProducts = $query->with([
            'images' => function ($q) {
                $q->where('is_primary', true)->orWhere(function ($q2) {
                    $q2->limit(1);
                });
            }
        ])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return ProductResource::collection($relatedProducts);
    }
}
