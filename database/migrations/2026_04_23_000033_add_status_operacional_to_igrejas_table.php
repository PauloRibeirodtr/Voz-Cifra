<?php

use App\Enums\PapelIgreja;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->string('status_operacional', 40)
                ->default('aguardando_admin_local')
                ->after('imagem_path');

            $table->index('status_operacional');
        });

        DB::table('igrejas')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->each(function (object $igreja): void {
                $temAdminLocalAtivo = DB::table('usuario_igreja as ui')
                    ->join('usuarios as u', 'u.id', '=', 'ui.usuario_id')
                    ->join('usuario_igreja_papeis as uip', function ($join): void {
                        $join->on('uip.usuario_igreja_id', '=', 'ui.id')
                            ->where('uip.papel', '=', PapelIgreja::ADMIN_LOCAL->value)
                            ->where('uip.ativo', '=', true)
                            ->whereNull('uip.revogado_em');
                    })
                    ->where('ui.igreja_id', $igreja->id)
                    ->where('ui.ativo', true)
                    ->where('u.ativo', true)
                    ->exists();

                DB::table('igrejas')
                    ->where('id', $igreja->id)
                    ->update([
                        'status_operacional' => $temAdminLocalAtivo ? 'operacional' : 'aguardando_admin_local',
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->dropIndex(['status_operacional']);
            $table->dropColumn('status_operacional');
        });
    }
};
