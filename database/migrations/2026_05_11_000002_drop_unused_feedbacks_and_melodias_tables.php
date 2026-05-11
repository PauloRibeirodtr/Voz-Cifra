<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('versoes_musicais', 'melodia_id')) {
            Schema::table('versoes_musicais', function (Blueprint $table): void {
                if ($this->indexExists('versoes_musicais', 'versoes_musicais_musica_id_melodia_id_index')) {
                    $table->dropIndex('versoes_musicais_musica_id_melodia_id_index');
                }

                $table->dropConstrainedForeignId('melodia_id');

                if (!$this->indexExists('versoes_musicais', 'versoes_musicais_musica_id_index')) {
                    $table->index('musica_id');
                }
            });
        }

        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('melodias');
    }

    public function down(): void
    {
        if (!Schema::hasTable('melodias')) {
            Schema::create('melodias', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('musica_id')->constrained('musicas')->cascadeOnDelete();
                $table->string('nome');
                $table->text('descricao')->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();

                $table->unique(['musica_id', 'nome']);
                $table->index('ativo');
            });
        }

        if (!Schema::hasColumn('versoes_musicais', 'melodia_id')) {
            Schema::table('versoes_musicais', function (Blueprint $table): void {
                $table->foreignId('melodia_id')->nullable()->after('musica_id')->constrained('melodias')->nullOnDelete();
                $table->index(['musica_id', 'melodia_id']);
            });
        }

        if (!Schema::hasTable('feedbacks')) {
            Schema::create('feedbacks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
                $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->nullOnDelete();
                $table->string('tipo', 20)->default('sugestao');
                $table->text('mensagem');
                $table->string('status', 20)->default('novo');
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index(['tipo', 'created_at']);
                $table->index(['usuario_id', 'created_at']);
                $table->index(['igreja_id', 'created_at']);
            });
        }
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
