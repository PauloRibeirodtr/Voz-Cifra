<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missa_musicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missa_id')->constrained('missas')->cascadeOnDelete();
            $table->foreignId('musica_id')->constrained('musicas')->restrictOnDelete();
            $table->foreignId('versao_musical_id')->nullable()->constrained('versoes_musicais')->nullOnDelete();
            $table->string('tom_usado', 20)->nullable();
            $table->foreignId('momento_liturgico_id')->nullable()->constrained('classificacoes_liturgicas')->nullOnDelete();
            $table->integer('ordem');
            $table->timestamps();

            $table->unique(['missa_id', 'ordem']);
            $table->index(['missa_id', 'momento_liturgico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missa_musicas');
    }
};
