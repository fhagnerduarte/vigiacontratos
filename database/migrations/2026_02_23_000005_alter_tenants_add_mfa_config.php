<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('mfa_habilitado')->default(false)->after('plano');
            $table->string('mfa_modo')->default('opcional')->after('mfa_habilitado'); // desativado, opcional, obrigatorio
            $table->json('mfa_perfis_obrigatorios')->nullable()->after('mfa_modo');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['mfa_habilitado', 'mfa_modo', 'mfa_perfis_obrigatorios']);
        });
    }
};
