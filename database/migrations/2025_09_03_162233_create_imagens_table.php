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
        Schema::create('imagens', function (Blueprint $table) {
            $table->id(); // PK auto increment
            $table->foreignId('registro_id') -> constrained('registros');   // FK para tabela 'registros', relacionamento 1:N. por convenção registro_id liga com registros.id
            $table->string('path'); // Caminho das imagens [path]
            /**
             * Posição da imagem (enum).
             * - Definimos todas as posições possíveis (obrigatórias e opcionais).
             * - Isso dá segurança no banco: só valores válidos são permitidos.
             * - As obrigatórias (comentadas com *) são garantidas na validação (FormRequest),
             *   e não aqui no banco.
             */
            $table->enum('posicao', [
                // Carro (obrigatórias *)
                'frente',                // *
                'lado_direito',          // *
                'lado_esquerdo',         // *
                'traseira',              // *
                'capo_aberto',           // *
                'numero_do_motor',       // *
                'painel_lado_direito',   // *
                'painel_lado_esquerdo',  // *
                // Carro (opcionais)
                'bateria_carro',
                'chave_carro',
                'estepe_do_veiculo',

                // Moto (obrigatórias *)
                'motor_lado_direito',    // *
                'motor_lado_esquerdo',   // *
                'painel_moto',           // *
                // Moto (opcionais)
                'chave_moto',
                'bateria_moto',
            ]);

            $table->timestamps(); // created_at / updated_at

            /**
             * Índice único composto (registro_id + posicao).
             * - Garante que um mesmo registro não terá duas imagens
             *   para a mesma posição.
             * - Exemplo: não pode existir duas "frente" para o mesmo veículo.
             */
            $table->unique(['registro_id', 'posicao']);                     // Garante apenas 1 foto por posição dentro do mesmo registro
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagens');
    }
};
