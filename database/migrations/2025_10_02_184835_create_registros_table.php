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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->string('placa') -> unique();
            $table->foreignId('marca_id') -> constrained('marcas');
            $table->string('modelo');
            $table->text('observacao')->nullable();
            $table->text('reboque_condutor');
            $table->string('reboque_placa');
            $table->boolean('no_patio')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
