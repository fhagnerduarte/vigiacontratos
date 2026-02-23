<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_alteracoes', function (Blueprint $table) {
            $table->index('user_id', 'historico_alteracoes_user_id_index');
            $table->index('created_at', 'historico_alteracoes_created_at_index');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->index('created_at', 'login_logs_created_at_index');
            $table->index('success', 'login_logs_success_index');
        });

        Schema::table('log_acesso_documentos', function (Blueprint $table) {
            $table->index('created_at', 'log_acesso_documentos_created_at_index');
            $table->index('acao', 'log_acesso_documentos_acao_index');
        });
    }

    public function down(): void
    {
        Schema::table('historico_alteracoes', function (Blueprint $table) {
            $table->dropIndex('historico_alteracoes_user_id_index');
            $table->dropIndex('historico_alteracoes_created_at_index');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex('login_logs_created_at_index');
            $table->dropIndex('login_logs_success_index');
        });

        Schema::table('log_acesso_documentos', function (Blueprint $table) {
            $table->dropIndex('log_acesso_documentos_created_at_index');
            $table->dropIndex('log_acesso_documentos_acao_index');
        });
    }
};
