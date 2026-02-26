<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('plano');
            $table->string('cor_primaria', 7)->default('#1b55e2')->after('logo_path');
            $table->string('cor_secundaria', 7)->default('#0b3a9e')->after('cor_primaria');
            $table->string('endereco')->nullable()->after('cor_secundaria');
            $table->string('telefone', 20)->nullable()->after('endereco');
            $table->string('email_contato')->nullable()->after('telefone');
            $table->string('horario_atendimento')->nullable()->after('email_contato');
            $table->string('cnpj', 18)->nullable()->after('horario_atendimento');
            $table->string('gestor_nome')->nullable()->after('cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'cor_primaria',
                'cor_secundaria',
                'endereco',
                'telefone',
                'email_contato',
                'horario_atendimento',
                'cnpj',
                'gestor_nome',
            ]);
        });
    }
};
