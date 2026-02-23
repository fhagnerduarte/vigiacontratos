<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->boolean('is_irregular')->default(false)->after('status');
            $table->index('is_irregular');
        });

        Schema::table('aditivos', function (Blueprint $table) {
            $table->text('justificativa_retroativa')->nullable()->after('justificativa_excesso_limite');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropIndex(['is_irregular']);
            $table->dropColumn('is_irregular');
        });

        Schema::table('aditivos', function (Blueprint $table) {
            $table->dropColumn('justificativa_retroativa');
        });
    }
};
