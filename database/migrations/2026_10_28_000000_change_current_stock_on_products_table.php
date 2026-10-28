<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El stock estaba declarado como DOUBLE(8,2) UNSIGNED, así que cualquier descuento
     * que dejara el inventario en negativo lanzaba "Numeric value out of range" (1264)
     * y abortaba la venta a medias.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY current_stock DOUBLE NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY current_stock DOUBLE(8,2) UNSIGNED NULL DEFAULT NULL');
    }
};
