<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\SiteUser\CartItemResource;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShoppingCartController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $cart = ShoppingCart::firstOrCreate(['site_user_id' => $user->id]);

        $cartItems = ShoppingCartItem::with(['product' => function ($query) {
            $query->with(['images' => function ($imgQuery) {
                $imgQuery->where('is_primary', true)->orWhere(fn($q) => $q->limit(1));
            }, 'category']);
        }])
            ->where('shopping_cart_id', $cart->id)
            ->get();

        return CartItemResource::collection($cartItems);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $productId = $validated['product_id'];
        $quantityToAdd = $validated['qty'];

        $product = Product::find($productId);

        $cart = ShoppingCart::firstOrCreate(['site_user_id' => $user->id]);

        $cartItem = ShoppingCartItem::where('shopping_cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        $currentQtyInCart = $cartItem ? $cartItem->qty : 0;
        $requestedTotalQty = $currentQtyInCart + $quantityToAdd;

        if ($product->stock < $requestedTotalQty) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi untuk jumlah yang diminta (' . $requestedTotalQty . '). Stok tersedia: ' . $product->stock . '.'
            ], 422);
        }

        $cartItem = ShoppingCartItem::updateOrCreate(
            [
                'shopping_cart_id' => $cart->id,
                'product_id' => $productId,
            ],
            [
                'qty' => $requestedTotalQty
            ]
        );

        return response()->json(['message' => 'Produk berhasil ditambahkan di keranjang.'], 200);
    }

    public function update(UpdateCartItemRequest $request, $cartItemId): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $newQty = $validated['qty'];

        $cartItem = ShoppingCartItem::with('product')
            ->whereHas('shoppingCart', function ($query) use ($user) {
                $query->where('site_user_id', $user->id);
            })
            ->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['message' => 'Item keranjang tidak ditemukan.'], 404);
        }

        if ($cartItem->product->stock < $newQty) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $cartItem->product->stock . '.'
            ], 422);
        }

        $cartItem->update(['qty' => $newQty]);


        return response()->json(['message' => 'Jumlah produk berhasil diperbarui.'], 200);
    }

    public function destroy(Request $request, $cartItemId): JsonResponse
    {
        $user = $request->user();
        $cartItem = ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })
            ->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['message' => 'Item keranjang tidak ditemukan.'], 404);
        }

        $cartItem->delete();

        return response()->json(null, 204);
    }

    public function clearCart(Request $request): JsonResponse
    {
        $user = $request->user();

        ShoppingCartItem::whereHas('shoppingCart', function ($query) use ($user) {
            $query->where('site_user_id', $user->id);
        })->delete();

        return response()->json(null, 204);
    }
}
