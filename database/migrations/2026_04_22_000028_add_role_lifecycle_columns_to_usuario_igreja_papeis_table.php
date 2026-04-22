<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario_igreja_papeis', function (Blueprint $table) {
            $table->string('origem', 80)->nullable()->after('ativo');
            $table->foreignId('revogado_por')->nullable()->after('concedido_por')->constrained('usuarios')->nullOnDelete();
            $table->timestampTz('revogado_em')->nullable()->after('concedido_em');

            $table->index(['papel', 'ativo', 'revogado_em'], 'usuario_igreja_papeis_papel_ativo_revogado_index');
            $table->index('revogado_por', 'usuario_igreja_papeis_revogado_por_index');
        });
    }

    public function down(): void
    {
        Schema::table('usuario_igreja_papeis', function (Blueprint $table) {
            $table->dropIndex('usuario_igreja_papeis_papel_ativo_revogado_index');
            $table->dropIndex('usuario_igreja_papeis_revogado_por_index');
            $table->dropConstrainedForeignId('revogado_por');
            $table->dropColumn(['origem', 'revogado_em']);
        });
    }
};
