<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_user_id')->constrained('site_users')->onDelete('cascade'); 
            $table->foreignId('address_id')->constrained('addresses')->onDelete('restrict');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('shipping_cost', 15, 2);
            $table->string('status')->default(OrderStatus::PENDING->value)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
