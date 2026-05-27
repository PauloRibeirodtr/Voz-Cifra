<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes_internas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('ator_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->nullOnDelete();
            $table->string('tipo', 60);
            $table->string('titulo');
            $table->text('mensagem')->nullable();
            $table->string('url')->nullable();
            $table->json('dados')->nullable();
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'lida_em', 'created_at']);
            $table->index(['tipo', 'created_at']);
            $table->index(['igreja_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes_internas');
    }
};
