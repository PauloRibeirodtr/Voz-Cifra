<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('igrejas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('slug_publico_musicos')->nullable()->unique();
            $table->string('cnpj', 18);
            $table->string('cep', 9)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('imagem_path')->nullable();
            $table->string('status_operacional', 40)->default('aguardando_admin_local');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['cidade', 'estado']);
            $table->index('ativo');
            $table->index('slug');
            $table->index('status_operacional');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('igrejas');
    }
};
