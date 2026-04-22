<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('usuarios')
            || !Schema::hasTable('usuario_igreja')
            || !Schema::hasTable('usuario_igreja_papeis')
        ) {
            return;
        }

        $agora = now();

        DB::transaction(function () use ($agora): void {
            $usuarios = DB::table('usuarios')
                ->select('id', 'igreja_id', 'perfil_global', 'nivel_global', 'created_at')
                ->whereNotNull('igreja_id')
                ->where('perfil_global', '!=', 'admin_master')
                ->orderBy('id')
                ->get();

            foreach ($usuarios as $usuario) {
                $vinculo = DB::table('usuario_igreja')
                    ->where('usuario_id', $usuario->id)
                    ->where('igreja_id', $usuario->igreja_id)
                    ->first();

                $possuiResponsavelPrincipal = DB::table('usuario_igreja')
                    ->where('usuario_id', $usuario->id)
                    ->where('responsavel_principal', true)
                    ->exists();

                if (!$vinculo) {
                    $vinculoId = DB::table('usuario_igreja')->insertGetId([
                        'usuario_id' => $usuario->id,
                        'igreja_id' => $usuario->igreja_id,
                        'ativo' => true,
                        'responsavel_principal' => !$possuiResponsavelPrincipal,
                        'vinculado_em' => $usuario->created_at ?? $agora,
                        'desvinculado_em' => null,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ]);
                } else {
                    $dadosAtualizacaoVinculo = [
                        'ativo' => true,
                        'desvinculado_em' => null,
                        'updated_at' => $agora,
                    ];

                    if (!$possuiResponsavelPrincipal) {
                        $dadosAtualizacaoVinculo['responsavel_principal'] = true;
                    }

                    DB::table('usuario_igreja')
                        ->where('id', $vinculo->id)
                        ->update($dadosAtualizacaoVinculo);

                    $vinculoId = (int) $vinculo->id;
                }

                foreach ($this->resolverPapeisLegados($usuario->perfil_global, (int) $usuario->nivel_global) as $papel) {
                    $papelExistente = DB::table('usuario_igreja_papeis')
                        ->where('usuario_igreja_id', $vinculoId)
                        ->where('papel', $papel)
                        ->first();

                    if (!$papelExistente) {
                        DB::table('usuario_igreja_papeis')->insert([
                            'usuario_igreja_id' => $vinculoId,
                            'papel' => $papel,
                            'ativo' => true,
                            'origem' => 'migracao_legado_perfis',
                            'concedido_por' => null,
                            'revogado_por' => null,
                            'concedido_em' => $usuario->created_at ?? $agora,
                            'revogado_em' => null,
                            'created_at' => $agora,
                            'updated_at' => $agora,
                        ]);

                        continue;
                    }

                    DB::table('usuario_igreja_papeis')
                        ->where('id', $papelExistente->id)
                        ->update([
                            'ativo' => true,
                            'origem' => $papelExistente->origem ?: 'migracao_legado_perfis',
                            'revogado_por' => null,
                            'revogado_em' => null,
                            'updated_at' => $agora,
                        ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('usuario_igreja_papeis')
            ->where('origem', 'migracao_legado_perfis')
            ->delete();
    }

    private function resolverPapeisLegados(string $perfilGlobal, int $nivelGlobal): array
    {
        $papeis = [];

        if ($perfilGlobal === 'member') {
            $papeis[] = 'musico';
        }

        if ($perfilGlobal === 'admin_local' || $nivelGlobal === 5) {
            $papeis[] = 'admin_local';
        }

        return array_values(array_unique($papeis));
    }
};
