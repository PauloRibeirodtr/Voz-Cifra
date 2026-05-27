<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_mudanca_tom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missa_musica_id')->constrained('missa_musicas')->cascadeOnDelete();
            $table->foreignId('missa_id')->constrained('missas')->cascadeOnDelete();
            $table->foreignId('igreja_id')->constrained('igrejas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('tom_atual', 20)->nullable();
            $table->string('tom_sugerido', 20);
            $table->text('observacao')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->text('resposta')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('revisado_em')->nullable();
            $table->timestamps();

            $table->index(['igreja_id', 'status', 'created_at']);
            $table->index(['missa_musica_id', 'status']);
            $table->index(['usuario_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_mudanca_tom');
    }
};
