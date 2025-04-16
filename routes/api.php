<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ADMIN USER CONTROLLER
use App\Http\Controllers\AdminUser\AdminUserController;
use App\Http\Controllers\AdminUser\AdminAuthController;
use App\Http\Controllers\AdminUser\CategoryController;
use App\Http\Controllers\AdminUser\DashboardController;
use App\Http\Controllers\AdminUser\ProductController;
use App\Http\Controllers\AdminUser\ReportController;
use App\Http\Controllers\AdminUser\SiteUserController;
use App\Http\Controllers\AdminUser\OrderController as AdminUserOrderController;
use App\Http\Controllers\AdminUser\PaymentController as AdminUserPaymentController;
use App\Http\Controllers\AdminUser\ShipmentController as AdminUserShipmentController;
use App\Http\Controllers\AdminUser\ProductReviewController as AdminUserProductReviewController;
// SITE USER CONTROLLER
use App\Http\Controllers\SiteUser\AuthController;
use App\Http\Controllers\SiteUser\AddressController;
use App\Http\Controllers\SiteUser\ShoppingCartController;
use App\Http\Controllers\SiteUser\ForgotPasswordController;
use App\Http\Controllers\SiteUser\OrderController as SiteUserOrderController;
use App\Http\Controllers\SiteUser\PaymentController as SiteUserPaymentController;
use App\Http\Controllers\SiteUser\ShipmentController as SiteUserShipmentController;
use App\Http\Controllers\SiteUser\ProductController as SiteUserProductController;
use App\Http\Controllers\SiteUser\ProductReviewController as SiteUserProductReviewController;

Route::middleware('guest:sanctum')->group(function () {
    Route::post('/admin/login', [AdminAuthController::class, 'login']);

    Route::post('/user/register', [AuthController::class, 'register']);
    Route::post('/user/login', [AuthController::class, 'login']);
});

// ADMIN USER
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/admin/profile', [AdminAuthController::class, 'profile']);
    Route::put('/admin/profile', [AdminAuthController::class, 'updateProfile']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    
    Route::get('/admin/admin', [AdminUserController::class, 'index']);
    Route::post('/admin/admin', [AdminUserController::class, 'store']);
    Route::get('/admin/admin/{admin_user}', [AdminUserController::class, 'show']);
    Route::put('/admin/admin/{admin_user}', [AdminUserController::class, 'update']);
    Route::delete('/admin/admin/{admin_user}', [AdminUserController::class, 'destroy']);


    // SiteUserDetailController
    Route::get('/admin/site_user', [SiteUserController::class, 'index']);
    Route::get('/admin/site_user/{siteUser}', [SiteUserController::class, 'show']);
    Route::put('/admin/update_siteuser_status/{siteUser}', [SiteUserController::class, 'updateStatus']);

    // Dashboard
    Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/admin/dashboard/orders_data', [DashboardController::class, 'ordersData']);
    Route::get('/admin/dashboard/sales_data', [DashboardController::class, 'salesData']);
    Route::get('/admin/dashboard/recent_orders', [DashboardController::class, 'recentOrders']);

    // Category
    Route::get('/admin/category', [CategoryController::class, 'index']);
    Route::post('/admin/category', [CategoryController::class, 'store']);
    Route::get('/admin/category/{category}', [CategoryController::class, 'show']);
    Route::put('/admin/category/{category}', [CategoryController::class, 'update']);
    Route::delete('/admin/category/{category}', [CategoryController::class, 'destroy']);

    // Product
    Route::get('/admin/product', [ProductController::class, 'index']);
    Route::post('/admin/product', [ProductController::class, 'store']);
    Route::get('/admin/product/{product}', [ProductController::class, 'show']);
    Route::put('/admin/product/{product}', [ProductController::class, 'update']);
    Route::delete('/admin/product/{product}', [ProductController::class, 'destroy']);

    // Order
    Route::get('/admin/orders', [AdminUserOrderController::class, 'index']);
    Route::get('/admin/orders/{order}', [AdminUserOrderController::class, 'show']);
    Route::put('/admin/orders/{order}', [AdminUserOrderController::class, 'updateStatus']);

    // Payment
    Route::get('/admin/payments', [AdminUserPaymentController::class, 'index']);
    Route::get('/admin/payments/{payment}', [AdminUserPaymentController::class, 'show']);

    // Shipment
    Route::get('/admin/shipments', [AdminUserShipmentController::class, 'index']);
    Route::get('/admin/shipments/{shipment}', [AdminUserShipmentController::class, 'show']);
    Route::put('/admin/shipments/{shipment}', [AdminUserShipmentController::class, 'update']);

    // Proudct Review
    Route::get('/admin/reviews', [AdminUserProductReviewController::class, 'index']);
    Route::get('/admin/reviews/{id}', [AdminUserProductReviewController::class, 'show']);

    // Report
    Route::get('/admin/reports', [ReportController::class, 'index']);
});

// SITE USER
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user/update', [AuthController::class, 'updateUser']);

    // Shopping Cart
    Route::delete('/user/shopping_cart/clear', [ShoppingCartController::class, 'clearCart']);
    Route::get('/user/shopping_cart', [ShoppingCartController::class, 'index']);
    Route::post('/user/shopping_cart', [ShoppingCartController::class, 'store']);
    Route::put('/user/shopping_cart/{cartItemId}', [ShoppingCartController::class, 'update']);
    Route::delete('/user/shopping_cart/{cartItemId}', [ShoppingCartController::class, 'destroy']);

    // Address
    Route::get('user/addresses', [AddressController::class, 'index']);
    Route::post('user/addresses', [AddressController::class, 'store']);
    Route::get('user/addresses/{address}', [AddressController::class, 'show']);
    Route::put('user/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('user/addresses/{address}', [AddressController::class, 'destroy']);
    Route::patch('user/addresses/{address}/set-default', [AddressController::class, 'setDefault']);

    // Order
    Route::get('user/orders', [SiteUserOrderController::class, 'index']);
    Route::get('user/orders/{order}', [SiteUserOrderController::class, 'show']);
    Route::post('user/orders/{order}/confirm-delivery', [SiteUserOrderController::class, 'confirmDelivery'])->name('orders.confirmDelivery');

    // Proudct Review
    Route::get('user/products/{productId}/review-eligibility', [SiteUserProductReviewController::class, 'eligibility']);
    Route::post('user/products/{productId}/reviews', [SiteUserProductReviewController::class, 'store']);
    Route::put('user/reviews/{review}', [SiteUserProductReviewController::class, 'update']);
    Route::patch('user/reviews/{review}', [SiteUserProductReviewController::class, 'update']);
    Route::delete('user/reviews/{review}', [SiteUserProductReviewController::class, 'destroy']);

    // Payment
    Route::post('/midtrans/snap-token', [SiteUserPaymentController::class, 'initiatePayment']);

    // Shipping Cost
    Route::post('/calculate-shipping-cost', [SiteUserShipmentController::class, 'calculateShippingCost']);
});

Route::post('/midtrans/notification', [SiteUserPaymentController::class, 'handleNotification']);

Route::get('/user/get_categories', [SiteUserProductController::class, 'getAllCategories']);
Route::get('/user/get_products', [SiteUserProductController::class, 'getAllProducts']);
Route::get('/user/get_latest_products', [SiteUserProductController::class, 'getLatestProducts']);
Route::get('/user/product/{product:slug}/detail', [SiteUserProductController::class, 'getProductDetail']);
Route::get('/user/get_related_products', [SiteUserProductController::class, 'getRelatedProducts']);

// Proudct Review
Route::get('user/products/{productId}/reviews', [SiteUserProductReviewController::class, 'index']);
Route::get('user/reviews/featured', [SiteUserProductReviewController::class, 'getFeaturedReviews']);

// Forgot Password
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ForgotPasswordController::class, 'reset']);
