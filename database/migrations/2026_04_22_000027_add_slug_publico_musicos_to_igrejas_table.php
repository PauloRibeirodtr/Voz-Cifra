<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->string('slug_publico_musicos')->nullable()->after('slug');
        });

        $igrejas = DB::table('igrejas')
            ->select('id', 'slug', 'slug_publico_musicos')
            ->orderBy('id')
            ->get();

        $slugsEmUso = [];

        foreach ($igrejas as $igreja) {
            $slugAtual = trim((string) ($igreja->slug_publico_musicos ?? ''));

            if ($slugAtual !== '') {
                $slugsEmUso[$slugAtual] = true;
            }
        }

        foreach ($igrejas as $igreja) {
            if (!empty($igreja->slug_publico_musicos)) {
                continue;
            }

            $base = Str::slug((string) $igreja->slug);
            $base = $base !== '' ? $base . '-musicos' : 'igreja-' . $igreja->id . '-musicos';

            $slugPublicoMusicos = $base;
            $contador = 2;

            while (isset($slugsEmUso[$slugPublicoMusicos])) {
                $slugPublicoMusicos = $base . '-' . $contador;
                $contador++;
            }

            DB::table('igrejas')
                ->where('id', $igreja->id)
                ->update([
                    'slug_publico_musicos' => $slugPublicoMusicos,
                ]);

            $slugsEmUso[$slugPublicoMusicos] = true;
        }

        Schema::table('igrejas', function (Blueprint $table) {
            $table->unique('slug_publico_musicos', 'igrejas_slug_publico_musicos_unique');
            $table->index('slug_publico_musicos', 'igrejas_slug_publico_musicos_index');
        });
    }

    public function down(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->dropUnique('igrejas_slug_publico_musicos_unique');
            $table->dropIndex('igrejas_slug_publico_musicos_index');
            $table->dropColumn('slug_publico_musicos');
        });
    }
};
