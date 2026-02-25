<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Novos campos no contrato: valor_empenhado e saldo_contratual
        Schema::table('contratos', function (Blueprint $table) {
            $table->decimal('valor_empenhado', 15, 2)->nullable()->after('numero_empenho');
            $table->decimal('saldo_contratual', 15, 2)->nullable()->after('valor_empenhado');
        });

        // Novos campos na execucao financeira: tipo, numero_empenho, competencia
        Schema::table('execucoes_financeiras', function (Blueprint $table) {
            $table->string('tipo_execucao', 30)->default('pagamento')->after('contrato_id');
            $table->string('numero_empenho', 50)->nullable()->after('numero_nota_fiscal');
            $table->string('competencia', 7)->nullable()->after('numero_empenho');
        });
    }

    public function down(): void
    {
        Schema::table('execucoes_financeiras', function (Blueprint $table) {
            $table->dropColumn(['tipo_execucao', 'numero_empenho', 'competencia']);
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn(['valor_empenhado', 'saldo_contratual']);
        });
    }
};
