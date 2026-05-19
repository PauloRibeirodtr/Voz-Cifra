<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('musicas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('artista')->nullable();
            $table->text('letra');
            $table->foreignId('momento_liturgico_id')->nullable()->constrained('classificacoes_liturgicas')->nullOnDelete();
            $table->foreignId('tempo_liturgico_id')->nullable()->constrained('classificacoes_liturgicas')->nullOnDelete();
            $table->foreignId('criado_por')->constrained('usuarios')->restrictOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('titulo');
            $table->index('ativo');
            $table->index(['momento_liturgico_id', 'tempo_liturgico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('musicas');
    }
};
