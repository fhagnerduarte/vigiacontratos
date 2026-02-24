<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fornecedores — campos sensiveis precisam de TEXT para armazenar valores criptografados
        Schema::connection('tenant')->table('fornecedores', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->text('telefone')->nullable()->change();
            $table->text('representante_legal')->nullable()->change();
        });

        // Fiscais — email sensivel
        Schema::connection('tenant')->table('fiscais', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
        });

        // Login Logs — IP e User-Agent sao dados pessoais tecnicos (LGPD)
        Schema::connection('tenant')->table('login_logs', function (Blueprint $table) {
            $table->text('ip_address')->change();
            $table->text('user_agent')->change();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('fornecedores', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
            $table->string('telefone', 20)->nullable()->change();
            $table->string('representante_legal', 255)->nullable()->change();
        });

        Schema::connection('tenant')->table('fiscais', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
        });

        Schema::connection('tenant')->table('login_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->change();
            $table->string('user_agent', 512)->change();
        });
    }
};
