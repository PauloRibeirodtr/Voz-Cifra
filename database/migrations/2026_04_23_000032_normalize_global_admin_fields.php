<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('usuarios')) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('usuarios')
                ->where('perfil_global', '!=', 'admin_master')
                ->update([
                    'perfil_global' => 'usuario',
                ]);

            DB::table('usuarios')
                ->where('perfil_global', 'admin_master')
                ->update([
                    'nivel_global' => 6,
                ]);

            DB::table('usuarios')
                ->where('perfil_global', '!=', 'admin_master')
                ->update([
                    'nivel_global' => 1,
                ]);
        });
    }

    public function down(): void
    {
        // Normalizacao irreversivel por tratar legado inconsistente.
    }
};
