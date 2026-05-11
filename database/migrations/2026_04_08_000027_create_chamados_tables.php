<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo')->unique();
            $table->foreignId('auditoria_evento_id')->nullable()->constrained('auditoria_eventos')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao');
            $table->string('status', 30)->default('aberto');
            $table->string('prioridade', 30)->default('media');
            $table->string('categoria', 60)->default('outro');
            $table->string('canal_origem', 60)->nullable();
            $table->string('origem_tipo', 60)->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->foreignId('solicitante_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('solicitante_nome')->nullable();
            $table->string('solicitante_email')->nullable();
            $table->foreignId('responsavel_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->nullOnDelete();
            $table->string('igreja_nome')->nullable();
            $table->timestamp('ultima_interacao_em')->nullable();
            $table->timestamp('resolvido_em')->nullable();
            $table->timestamp('fechado_em')->nullable();
            $table->text('resolucao_resumo')->nullable();
            $table->unsignedTinyInteger('avaliacao_nota')->nullable();
            $table->text('avaliacao_comentario')->nullable();
            $table->timestamps();

            $table->index(['status', 'ultima_interacao_em']);
            $table->index(['prioridade', 'status']);
            $table->index(['categoria', 'status']);
            $table->index(['solicitante_usuario_id', 'created_at']);
            $table->index(['igreja_id', 'created_at']);
            $table->index(['origem_tipo', 'origem_id']);
        });

        Schema::create('chamado_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamado_id')->constrained('chamados')->cascadeOnDelete();
            $table->foreignId('autor_usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('autor_nome')->nullable();
            $table->string('origem', 30)->default('usuario');
            $table->string('canal', 60)->nullable();
            $table->boolean('interno')->default(false);
            $table->text('mensagem');
            $table->timestamps();

            $table->index(['chamado_id', 'created_at']);
            $table->index(['interno', 'created_at']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE chamados ADD CONSTRAINT chamados_status_check CHECK (status IN ('aberto', 'em_andamento', 'aguardando_usuario', 'resolvido', 'fechado'))");
            DB::statement("ALTER TABLE chamados ADD CONSTRAINT chamados_prioridade_check CHECK (prioridade IN ('media', 'alta', 'critica'))");
            DB::statement("ALTER TABLE chamado_mensagens ADD CONSTRAINT chamado_mensagens_origem_check CHECK (origem IN ('usuario', 'suporte', 'sistema'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE chamado_mensagens DROP CONSTRAINT IF EXISTS chamado_mensagens_origem_check');
            DB::statement('ALTER TABLE chamados DROP CONSTRAINT IF EXISTS chamados_prioridade_check');
            DB::statement('ALTER TABLE chamados DROP CONSTRAINT IF EXISTS chamados_status_check');
        }

        Schema::dropIfExists('chamado_mensagens');
        Schema::dropIfExists('chamados');
    }
};
