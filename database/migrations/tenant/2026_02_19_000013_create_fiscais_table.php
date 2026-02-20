<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->string('nome', 255);
            $table->string('matricula', 50);
            $table->string('cargo', 255);
            $table->string('email', 255)->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('is_atual')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscais');
    }
};
