<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // JSON array of size variants: [{"label":"Small","price":70}, ...]
            // When populated, the base `price` column holds the starting/minimum price.
            // When null/empty, the base `price` column is the sole price.
            $table->json('sizes')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('sizes');
        });
    }
};
