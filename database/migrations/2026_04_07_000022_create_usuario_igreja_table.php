<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_igreja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('igreja_id')->constrained('igrejas')->cascadeOnDelete();
            $table->boolean('ativo')->default(true);
            $table->boolean('responsavel_principal')->default(false);
            $table->timestampTz('vinculado_em')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestampTz('desvinculado_em')->nullable();
            $table->timestampsTz();

            $table->unique(['usuario_id', 'igreja_id'], 'usuario_igreja_usuario_id_igreja_id_unique');
            $table->index(['igreja_id', 'ativo'], 'usuario_igreja_igreja_id_ativo_index');
            $table->index(['usuario_id', 'ativo'], 'usuario_igreja_usuario_id_ativo_index');
            $table->index(['igreja_id', 'responsavel_principal'], 'usuario_igreja_responsavel_principal_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_igreja');
    }
};
