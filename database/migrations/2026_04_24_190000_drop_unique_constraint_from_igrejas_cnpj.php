<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('igrejas', 'igrejas_cnpj_unique')) {
            return;
        }

        Schema::table('igrejas', function (Blueprint $table): void {
            $table->dropUnique('igrejas_cnpj_unique');
        });
    }

    public function down(): void
    {
        if ($this->indexExists('igrejas', 'igrejas_cnpj_unique')) {
            return;
        }

        Schema::table('igrejas', function (Blueprint $table): void {
            $table->unique('cnpj');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        return match ($driver) {
            'pgsql' => DB::table('pg_indexes')
                ->where('schemaname', 'public')
                ->where('tablename', $table)
                ->where('indexname', $index)
                ->exists(),
            'mysql' => DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists(),
            'sqlite' => collect(DB::select("PRAGMA index_list('$table')"))
                ->contains(fn ($row) => ($row->name ?? null) === $index),
            default => false,
        };
    }
};
