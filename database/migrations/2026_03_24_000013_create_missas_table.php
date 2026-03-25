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
            $table->foreignId('padre_id')->nullable()->constrained('padres')->nullOnDelete();
            $table->foreignId('tempo_liturgico_id')->nullable()->constrained('tempos_liturgicos')->nullOnDelete();
            $table->string('titulo');
            $table->date('data_missa');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo');
            $table->index(['igreja_id', 'data_missa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missas');
    }
};
