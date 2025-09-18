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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();                               // PK autoincrement (bigint)
            $table->string('model_type');               // FQCN do modelo afetado (ex.: App\Models\User)
            $table->unsignedBigInteger('model_id');     // ID do registro afetado nesse modelo
            $table->string('action');                   // Ação (ex.: created, updated, role-attached, etc.)
            $table->unsignedBigInteger('user_id')->nullable(); // Quem causou (opcional)
            $table->text('description')->nullable();    // Texto livre sobre o evento
            $table->json('changes')->nullable();        // Diferenças/extra em JSON (opcional)
            $table->timestamps();                       // created_at / updated_at

            // Índices para acelerar as consultas mais comuns:
            $table->index(['model_type', 'model_id']);  // buscar logs de um registro específico
            $table->index('action');                    // filtrar por ação
            $table->index('user_id');                   // filtrar por usuário que causou
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
