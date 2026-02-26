<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precos_referenciais', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 255);
            $table->string('categoria_servico', 30);
            $table->string('unidade_medida', 50);
            $table->decimal('preco_minimo', 15, 2);
            $table->decimal('preco_mediano', 15, 2);
            $table->decimal('preco_maximo', 15, 2);
            $table->string('fonte', 255);
            $table->date('data_referencia');
            $table->date('vigencia_ate')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_ativo')->default(true);
            $table->timestamps();

            $table->index('categoria_servico');
            $table->index('is_ativo');
            $table->index('data_referencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precos_referenciais');
    }
};
