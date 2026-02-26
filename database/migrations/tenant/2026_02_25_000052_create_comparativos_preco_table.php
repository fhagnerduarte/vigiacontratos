<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparativos_preco', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->foreignId('preco_referencial_id')->constrained('precos_referenciais')->cascadeOnDelete();
            $table->decimal('valor_contrato', 15, 2);
            $table->decimal('valor_referencia', 15, 2);
            $table->decimal('percentual_diferenca', 8, 2);
            $table->string('status_comparativo', 20);
            $table->text('observacoes')->nullable();
            $table->foreignId('gerado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('contrato_id');
            $table->index('preco_referencial_id');
            $table->index('status_comparativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparativos_preco');
    }
};
