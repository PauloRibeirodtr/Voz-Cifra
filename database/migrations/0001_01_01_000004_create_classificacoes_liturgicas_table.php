<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classificacoes_liturgicas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('ordem_exibicao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['tipo', 'nome'], 'classificacoes_liturgicas_tipo_nome_unique');
            $table->index(['tipo', 'ativo'], 'classificacoes_liturgicas_tipo_ativo_index');
            $table->index(['tipo', 'ordem_exibicao'], 'classificacoes_liturgicas_tipo_ordem_index');
            $table->index('ativo');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE classificacoes_liturgicas
                ADD CONSTRAINT classificacoes_liturgicas_tipo_check
                CHECK (tipo IN ('tempo', 'momento'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE classificacoes_liturgicas DROP CONSTRAINT IF EXISTS classificacoes_liturgicas_tipo_check');
        }

        Schema::dropIfExists('classificacoes_liturgicas');
    }
};
