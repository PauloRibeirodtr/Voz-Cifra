<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('igreja_id')->constrained('igrejas')->cascadeOnDelete();
            $table->foreignId('celebrante_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('tempo_liturgico_id')->nullable()->constrained('classificacoes_liturgicas')->nullOnDelete();
            $table->string('titulo');
            $table->date('data_missa');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->text('observacoes')->nullable();
            $table->boolean('publica_para_fieis')->default(false);
            $table->boolean('publica_para_musicos')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo');
            $table->index(['igreja_id', 'data_missa']);
            $table->index(['celebrante_usuario_id', 'data_missa']);
            $table->index(
                ['igreja_id', 'publica_para_fieis', 'ativo', 'data_missa'],
                'missas_publica_para_fieis_lookup_index'
            );
            $table->index(
                ['igreja_id', 'publica_para_musicos', 'ativo', 'data_missa'],
                'missas_publica_para_musicos_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missas');
    }
};
