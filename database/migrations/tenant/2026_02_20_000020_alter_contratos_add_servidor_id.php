<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->foreignId('servidor_id')->nullable()->after('gestor_nome')
                  ->constrained('servidores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servidor_id');
        });
    }
};
