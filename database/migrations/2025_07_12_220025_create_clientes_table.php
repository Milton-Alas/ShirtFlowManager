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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre del cliente');
            $table->string('telefono', 11)->nullable()->comment('Teléfono del cliente');
            $table->string('direccion')->nullable()->comment('Dirección del cliente');
            $table->boolean('es_frecuente')->default(false)->comment('Indica si el cliente es frecuente');
            $table->string('nota')->nullable()->comment('Nota adicional sobre el cliente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
