<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscais', function (Blueprint $table) {
            $table->foreignId('servidor_id')
                ->nullable()
                ->after('contrato_id')
                ->constrained('servidores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fiscais', function (Blueprint $table) {
            $table->dropForeign(['servidor_id']);
            $table->dropColumn('servidor_id');
        });
    }
};
