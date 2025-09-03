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
        Schema:: create ('registros_itens', function (Blueprint $table){
            $table -> foreignId('registros_id') -> constrained('registros');
            $table -> foreignId('itens_id') -> constrained('itens');
            $table -> primary (['registros_id', 'itens_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
