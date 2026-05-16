<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('cuisine_types')->nullable(); // array of strings
            $table->string('image')->nullable();
            // Address
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            // Delivery
            $table->decimal('delivery_radius', 8, 2)->default(5.00); // km
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->decimal('min_order_amount', 8, 2)->default(0.00);
            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            // Operations
            $table->json('opening_hours')->nullable(); // { monday: { open: "09:00", close: "22:00" }, ... }
            $table->unsignedInteger('estimated_delivery_time')->default(30); // minutes
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
