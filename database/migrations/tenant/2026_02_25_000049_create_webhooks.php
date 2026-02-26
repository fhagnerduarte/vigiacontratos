<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->json('eventos');
            $table->string('secret', 64);
            $table->boolean('is_ativo')->default(true);
            $table->string('descricao', 255)->nullable();
            $table->timestamp('ultimo_disparo_em')->nullable();
            $table->unsignedSmallInteger('ultimo_status_code')->nullable();
            $table->unsignedTinyInteger('falhas_consecutivas')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
