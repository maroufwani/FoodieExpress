<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('restaurant_id')->constrained('restaurants');
            $table->foreignId('delivery_partner_id')->nullable()->constrained('users')->nullOnDelete();
            // Delivery address snapshot
            $table->json('delivery_address'); // snapshot at time of order
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            // Payment
            $table->enum('payment_method', ['cash_on_delivery', 'card', 'digital_wallet', 'net_banking'])->default('cash_on_delivery');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            // Order status
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'out_for_delivery',
                'picked_up',
                'delivered',
                'cancelled',
            ])->default('pending');
            $table->text('special_instructions')->nullable();
            $table->unsignedInteger('estimated_delivery_time')->nullable(); // minutes
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
