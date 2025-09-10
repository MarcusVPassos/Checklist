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
            /**
             * FK para a tabela 'registros'.
             * - Cria coluna registros_id (BIGINT UNSIGNED).
             * - Cria índice automaticamente.
             * - Cria constraint FOREIGN KEY (registros_id → registros.id).
             */
            $table -> foreignId('registros_id') -> constrained('registros');

            /**
             * FK para a tabela 'itens'.
             * - Cria coluna itens_id (BIGINT UNSIGNED).
             * - Cria índice automaticamente.
             * - Cria constraint FOREIGN KEY (itens_id → itens.id).
             */
            $table -> foreignId('itens_id') -> constrained('itens');

            /**
             * Define chave primária composta.
             * - Garante que o mesmo item não seja vinculado duas vezes
             *   ao mesmo registro.
             * - Exemplo: não pode ter (registro 10, item 5) repetido.
             */
            $table -> primary (['registros_id', 'itens_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_itens');
    }
};
