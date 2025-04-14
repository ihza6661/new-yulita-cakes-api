<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function getAllCategories()
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }

    public function getAllProducts()
    {
        $products = Product::with(['images', 'category'])->orderBy('updated_at', 'desc')->get();
        return response()->json($products, 200);
    }

    public function getLatesProducts()
    {
        $products = Product::with(['images', 'category'])
            ->where('stock', '>', 0)
            ->latest()
            ->take(5)
            ->get();

        return response()->json($products, 200);
    }

    public function getProductDetail($slug)
    {
        $product = Product::with('images')->where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ], 200);
    }

    public function getRelatedProducts(Request $request)
    {
        // Jika menggunakan product_id untuk mengeluarkan produk yang sedang dilihat
        $productId = $request->query('product_id');
        $categoryId = $request->query('category_id'); // bisa juga dari produk yang sedang dilihat

        // Validasi input, minimal harus ada category_id
        if (!$categoryId) {
            return response()->json(['message' => 'Category ID wajib'], 400);
        }

        // Query untuk mendapatkan produk terkait dari kategori yang sama
        $query = Product::where('category_id', $categoryId);

        // Jika product_id ada, keluarkan produk tersebut dari hasil
        if ($productId) {
            $query->where('id', '<>', $productId);
        }

        // Sertakan relasi gambar dengan filter gambar utama (misalnya is_primary = 1)
        $relatedProducts = $query->with([
            'images' => function ($q) {
                $q->where('is_primary', 1);
            }
        ])
            ->take(5)
            ->get();

        return response()->json($relatedProducts);
    }

}
