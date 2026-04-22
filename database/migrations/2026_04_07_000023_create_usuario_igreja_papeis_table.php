<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_igreja_papeis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_igreja_id')->constrained('usuario_igreja')->cascadeOnDelete();
            $table->string('papel', 40);
            $table->boolean('ativo')->default(true);
            $table->foreignId('concedido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestampTz('concedido_em')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestampsTz();

            $table->unique(['usuario_igreja_id', 'papel'], 'usuario_igreja_papeis_vinculo_papel_unique');
            $table->index(['papel', 'ativo'], 'usuario_igreja_papeis_papel_ativo_index');
            $table->index(['usuario_igreja_id', 'ativo'], 'usuario_igreja_papeis_vinculo_ativo_index');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE usuario_igreja_papeis
                ADD CONSTRAINT usuario_igreja_papeis_papel_check
                CHECK (papel IN ('admin_local', 'coordenador', 'musico'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE usuario_igreja_papeis DROP CONSTRAINT IF EXISTS usuario_igreja_papeis_papel_check');
        }
        Schema::dropIfExists('usuario_igreja_papeis');
    }
};
