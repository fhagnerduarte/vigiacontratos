<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('contratos', function (Blueprint $table) {
            $table->string('classificacao_sigilo', 20)->default('publico')->after('link_transparencia');
            $table->unsignedBigInteger('classificado_por')->nullable()->after('classificacao_sigilo');
            $table->date('data_classificacao')->nullable()->after('classificado_por');
            $table->text('justificativa_sigilo')->nullable()->after('data_classificacao');
            $table->boolean('publicado_portal')->default(false)->after('justificativa_sigilo');

            $table->foreign('classificado_por')->references('id')->on('users')->nullOnDelete();
            $table->index('classificacao_sigilo');
            $table->index('publicado_portal');
        });

        Schema::connection('tenant')->table('documentos', function (Blueprint $table) {
            $table->string('classificacao_sigilo', 20)->default('publico')->after('uploaded_by');
            $table->text('justificativa_sigilo')->nullable()->after('classificacao_sigilo');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('contratos', function (Blueprint $table) {
            $table->dropForeign(['classificado_por']);
            $table->dropIndex(['classificacao_sigilo']);
            $table->dropIndex(['publicado_portal']);
            $table->dropColumn([
                'classificacao_sigilo',
                'classificado_por',
                'data_classificacao',
                'justificativa_sigilo',
                'publicado_portal',
            ]);
        });

        Schema::connection('tenant')->table('documentos', function (Blueprint $table) {
            $table->dropColumn([
                'classificacao_sigilo',
                'justificativa_sigilo',
            ]);
        });
    }
};
