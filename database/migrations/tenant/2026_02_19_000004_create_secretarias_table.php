<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('sigla', 20)->nullable();
            $table->string('responsavel', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretarias');
    }
};
