<?php

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
        Schema::table('variante_productos', function (Blueprint $table) {
            $table->unique(['producto_id', 'talla_id', 'color_id'], 'unique_variante_producto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variante_productos', function (Blueprint $table) {
            $table->dropUnique('unique_variante_producto');
        });
    }
};
