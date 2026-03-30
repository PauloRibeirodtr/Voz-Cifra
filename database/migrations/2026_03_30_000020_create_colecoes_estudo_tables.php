<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colecoes_estudo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('colecao_estudo_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colecao_estudo_id')->constrained('colecoes_estudo')->cascadeOnDelete();
            $table->foreignId('musica_id')->constrained('musicas')->cascadeOnDelete();
            $table->foreignId('versao_musical_id')->constrained('versoes_musicais')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['colecao_estudo_id', 'versao_musical_id'], 'colecao_estudo_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colecao_estudo_itens');
        Schema::dropIfExists('colecoes_estudo');
    }
};
