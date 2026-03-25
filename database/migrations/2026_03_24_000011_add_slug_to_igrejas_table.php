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
            $table->string('slug')->nullable();
        });

        $registros = DB::table('igrejas')
            ->select('id', 'nome')
            ->orderBy('id')
            ->get();

        $slugsUtilizados = [];

        foreach ($registros as $registro) {
            $base = Str::slug($registro->nome);
            $slugBase = $base !== '' ? $base : 'igreja';
            $slug = $slugBase;
            $contador = 2;

            while (in_array($slug, $slugsUtilizados, true) || DB::table('igrejas')->where('slug', $slug)->where('id', '!=', $registro->id)->exists()) {
                $slug = "{$slugBase}-{$contador}";
                $contador++;
            }

            DB::table('igrejas')
                ->where('id', $registro->id)
                ->update(['slug' => $slug]);

            $slugsUtilizados[] = $slug;
        }

        Schema::table('igrejas', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('slug');
        });

        DB::statement('ALTER TABLE igrejas ALTER COLUMN slug SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE igrejas ALTER COLUMN slug DROP NOT NULL');

        Schema::table('igrejas', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
