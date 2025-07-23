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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_gasto_id');
            $table->foreign('categoria_gasto_id')
                ->references('id')
                ->on('categoria_gastos')
                ->onDelete('cascade');
            $table->string('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('nota')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
