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
            $table->id();
            $table->foreignId('registro_id') -> constrained('registros');   // FK para tabela 'registros', relacionamento 1:N. por convenção registro_id liga com registros.id
            $table->string('public');                                       // Caminho das imagens [path]
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

            $table->timestamps();

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
