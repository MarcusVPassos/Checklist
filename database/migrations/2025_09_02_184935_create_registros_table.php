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
            $table->id();                                           // PK do Registro        
            $table->enum('tipo', [ 'carro' , 'moto' ]);             // Tipos de veículo para escolher                                            
            $table->string('placa') -> unique();                    // Placa única, para busca e evitar duplicidade                    
            $table->foreignId('marca_id')-> constrained('marcas');  // FK para tabela 'marcas', relacionamento 1:N. por convenção marca_id liga com marcas.id  
            $table->foreignId('user_id')-> constrained('users');     // FK para tabela 'user', relacionamento 1:N.                                   
            $table->string('modelo');                               // Modelo do Veículo        
            $table->text('observacao')->nullable();                 // Observações se necessitar no registro                    
            $table->text('reboque_condutor');                       // Nome do condutor do reboque                
            $table->string('reboque_placa');                        // Placa do reboque
            $table->string('assinatura_path');                      // Caminho do arquivo da assinatura;  [path]              
            $table->boolean('no_patio')->default(true);             // Tipo boolean para mudança de status ativo = no_patio e false = saiu      
            $table->softDeletes();                                  // Delete que só muda o status para deletado                  
            $table->timestamps();                                   // Quando criou e quando foi editado    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
