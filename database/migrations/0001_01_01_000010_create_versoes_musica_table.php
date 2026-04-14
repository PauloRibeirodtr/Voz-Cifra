<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versoes_musicais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('musica_id')->constrained('musicas')->cascadeOnDelete();
            $table->foreignId('melodia_id')->nullable()->constrained('melodias')->nullOnDelete();
            $table->string('titulo')->nullable();
            $table->string('tom_musical', 10)->nullable();
            $table->smallInteger('bpm')->nullable();
            $table->string('youtube_video_id', 32)->nullable();
            $table->text('letra_com_cifras');
            $table->foreignId('criado_por')->constrained('usuarios')->restrictOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo');
            $table->index(['musica_id', 'melodia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versoes_musicais');
    }
};
