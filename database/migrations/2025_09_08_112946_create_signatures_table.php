<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Essa tabela é usada pelo pacote Creagia Laravel Sign Pad
     * para armazenar informações de assinaturas feitas no sistema.
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id(); // PK auto increment
            /**
             * morphs('model') → cria duas colunas:
             * - model_id (BIGINT UNSIGNED)
             * - model_type (string)
             *
             * Isso habilita "Relacionamento polimórfico".
             * Assim, qualquer model da sua aplicação (ex: Registros)
             * pode ter assinaturas associadas.
             */
            $table->morphs('model');
            /**
             * Identificador único (UUID) para assinatura.
             * Usado pelo pacote para garantir unicidade e rastreabilidade.
             */
            $table->string('uuid');

            /**
             * Nome do arquivo da assinatura salva no storage.
             * Ex: "signatures/uuid123.png"
             */
            $table->string('filename');

            /**
             * Nome do arquivo de documento (PDF) gerado
             * junto da assinatura (opcional).
             */
            $table->string('document_filename')->nullable();

            /**
             * Flag booleana que indica se a assinatura foi
             * "certificada" (ex: validada com certificado digital).
             */
            $table->boolean('certified')->default(false);

            /**
             * Lista de IPs (JSON) de onde a assinatura foi realizada.
             * Permite rastrear múltiplos IPs caso o mesmo usuário
             * tenha assinado de locais diferentes.
             */
            $table->json('from_ips')->nullable();
            $table->timestamps(); // created_at / updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signatures');
    }
};
