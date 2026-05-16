<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('food_rating')->nullable()->after('delivered_at');
            $table->unsignedTinyInteger('delivery_rating')->nullable()->after('food_rating');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['food_rating', 'delivery_rating']);
        });
    }
};
