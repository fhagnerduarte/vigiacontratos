<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servidores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('cpf', 14)->nullable();
            $table->string('matricula', 50);
            $table->string('cargo', 255);
            $table->foreignId('secretaria_id')->nullable()
                  ->constrained('secretarias')->nullOnDelete();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->boolean('is_ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique('matricula');
            $table->unique('cpf');
            $table->index('secretaria_id');
            $table->index('is_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidores');
    }
};
