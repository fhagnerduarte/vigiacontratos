<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_login_logs', function (Blueprint $table) {
            $table->text('ip_address')->change();
            $table->text('user_agent')->change();
        });
    }

    public function down(): void
    {
        Schema::table('admin_login_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->change();
            $table->string('user_agent', 512)->change();
        });
    }
};
