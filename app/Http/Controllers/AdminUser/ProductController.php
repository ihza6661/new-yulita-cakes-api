<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $images = $request->file('images');

        unset($data['images']);

        DB::beginTransaction();
        try {
            $product = Product::create($data);

            if ($images) {
                foreach ($images as $index => $imageFile) {
                    $imagePath = $imageFile->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath,
                        'is_primary' => ($index === 0),
                    ]);
                }
            }

            DB::commit();

            $product->load('category', 'images');

            return response()->json([
                'message' => 'Produk berhasil ditambahkan.',
                'product' => new ProductResource($product)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing product: ' . $e->getMessage());

            if (isset($product) && $images) {
                $productImages = ProductImage::where('product_id', $product->id)->get();
                foreach ($productImages as $img) {
                    if (Storage::disk('public')->exists($img->image)) {
                        Storage::disk('public')->delete($img->image);
                    }
                }
            }


            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan produk.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['category', 'images']);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $newImageFiles = $request->file('images');
        $imagesToDeleteIds = $request->input('imagesToDelete', []);

        unset($data['images'], $data['imagesToDelete']);

        DB::beginTransaction();
        try {
            $product->update($data);

            if (!empty($imagesToDeleteIds)) {
                $imagesToDelete = ProductImage::where('product_id', $product->id)
                    ->whereIn('id', $imagesToDeleteIds)
                    ->get();

                foreach ($imagesToDelete as $productImage) {
                    if (Storage::disk('public')->exists($productImage->image)) {
                        Storage::disk('public')->delete($productImage->image);
                    }
                    $productImage->delete();
                }
            }

            if ($newImageFiles) {
                foreach ($newImageFiles as $imageFile) {
                    $imagePath = $imageFile->store('product', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath,
                        'is_primary' => false,
                    ]);
                }
            }

            $product->load('images');
            $currentImages = $product->images;

            $hasPrimary = $currentImages->contains('is_primary', true);

            if (!$hasPrimary && $currentImages->isNotEmpty()) {
                $firstImage = $currentImages->first();
                $firstImage->is_primary = true;
                $firstImage->save();
            }

            DB::commit();

            $product->load(['category', 'images']);

            return response()->json([
                'message' => 'Produk berhasil diperbarui.',
                'product' => new ProductResource($product)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui produk.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Product $product): Response
    {
        DB::beginTransaction();
        try {
            foreach ($product->images as $productImage) {
                if ($productImage->image && Storage::disk('public')->exists($productImage->image)) {
                    Storage::disk('public')->delete($productImage->image);
                }
            }

            $product->delete();

            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus produk.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
