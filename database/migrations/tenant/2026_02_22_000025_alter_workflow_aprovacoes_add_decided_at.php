<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_aprovacoes', function (Blueprint $table) {
            $table->timestamp('decided_at')->nullable()->after('parecer');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_aprovacoes', function (Blueprint $table) {
            $table->dropColumn('decided_at');
        });
    }
};
