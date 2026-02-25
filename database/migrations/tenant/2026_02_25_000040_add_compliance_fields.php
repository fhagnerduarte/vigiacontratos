<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->date('data_assinatura')->nullable()->after('data_inicio');
            $table->string('regime_execucao', 50)->nullable()->after('tipo_pagamento');
            $table->text('condicoes_pagamento')->nullable()->after('regime_execucao');
            $table->text('garantias')->nullable()->after('condicoes_pagamento');
            $table->date('data_publicacao')->nullable()->after('garantias');
            $table->string('veiculo_publicacao', 255)->nullable()->after('data_publicacao');
            $table->string('link_transparencia', 500)->nullable()->after('veiculo_publicacao');
        });

        Schema::table('fiscais', function (Blueprint $table) {
            $table->string('tipo_fiscal', 30)->default('titular')->after('is_atual');
            $table->string('portaria_designacao', 100)->nullable()->after('tipo_fiscal');
            $table->date('data_ultimo_relatorio')->nullable()->after('portaria_designacao');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'data_assinatura',
                'regime_execucao',
                'condicoes_pagamento',
                'garantias',
                'data_publicacao',
                'veiculo_publicacao',
                'link_transparencia',
            ]);
        });

        Schema::table('fiscais', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_fiscal',
                'portaria_designacao',
                'data_ultimo_relatorio',
            ]);
        });
    }
};
