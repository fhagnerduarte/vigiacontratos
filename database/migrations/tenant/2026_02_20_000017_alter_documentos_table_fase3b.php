<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Renomear colunas para alinhar com entidade documentada
            $table->renameColumn('path', 'caminho');
            $table->renameColumn('tamanho_bytes', 'tamanho');
        });

        Schema::table('documentos', function (Blueprint $table) {
            // Novas colunas para versionamento e Central de Documentos
            $table->string('nome_arquivo', 255)->after('nome_original');
            $table->string('descricao', 255)->nullable()->after('nome_arquivo');
            $table->unsignedInteger('versao')->default(1)->after('hash_integridade');
            $table->boolean('is_versao_atual')->default(true)->after('versao');

            // Tornar hash_integridade nullable (calculado no upload)
            $table->string('hash_integridade', 64)->nullable()->change();

            // Indices para performance (RN-131, busca e filtragem)
            $table->index('tipo_documento');
            $table->index('is_versao_atual');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex(['tipo_documento']);
            $table->dropIndex(['is_versao_atual']);

            $table->dropColumn(['nome_arquivo', 'descricao', 'versao', 'is_versao_atual']);

            $table->string('hash_integridade', 64)->nullable(false)->change();
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->renameColumn('caminho', 'path');
            $table->renameColumn('tamanho', 'tamanho_bytes');
        });
    }
};
