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
        Schema::create('variante_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('cascade')
                ->comment('ID del producto al que pertenece la variante');
            $table->foreignId('talla_id')
                ->constrained('tallas')
                ->onDelete('cascade')
                ->comment('ID de la talla asociada a la variante');
            $table->foreignId('color_id')
                ->constrained('colores')
                ->onDelete('cascade')
                ->comment('ID del color asociado a la variante');
            $table->decimal('precio', 10, 2)
                ->default(0.00)
                ->comment('Precio de la variante del producto');
            $table->integer('stock_docenas')
                ->default(0)
                ->comment('Cantidad de stock en docenas para la variante del producto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variante_productos');
    }
};
