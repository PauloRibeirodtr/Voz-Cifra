<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo', 40)->unique();
            $table->string('evento', 60);
            $table->string('categoria', 40)->default('seguranca');
            $table->foreignId('ator_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('ator_nome')->nullable();
            $table->string('ator_funcao')->nullable();
            $table->foreignId('alvo_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('alvo_nome')->nullable();
            $table->string('alvo_email')->nullable();
            $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->nullOnDelete();
            $table->string('igreja_nome')->nullable();
            $table->json('contexto')->nullable();
            $table->string('resultado', 30)->default('registrado');
            $table->timestamp('notificacao_enviada_em')->nullable();
            $table->text('erro_envio')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['evento', 'created_at']);
            $table->index(['alvo_id', 'created_at']);
            $table->index(['ator_id', 'created_at']);
            $table->index(['igreja_id', 'created_at']);
            $table->index(['resultado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_eventos');
    }
};
