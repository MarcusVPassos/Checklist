<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sobe a migration: cria a tabela pivô `registros_itens`
     * que mapeia o relacionamento N:N entre `registros` e `itens`.
     */
    public function up(): void
    {
        Schema:: create ('registros_itens', function (Blueprint $table){
            // FK para 'registros.id'. Usa o helper 'constrained()' para criar a constraint.
            $table -> foreignId('registros_id') -> constrained('registros');

            // FK para 'itens.id'.
            $table -> foreignId('itens_id') -> constrained('itens');

            // Define a PK composta (cada par registro+item é unico)
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
