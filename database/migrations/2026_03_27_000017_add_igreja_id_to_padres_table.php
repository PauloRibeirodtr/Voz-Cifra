<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->foreignId('igreja_id')
                ->nullable()
                ->after('cpf')
                ->constrained('igrejas')
                ->nullOnDelete();

            $table->index('igreja_id');
        });
    }

    public function down(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->dropConstrainedForeignId('igreja_id');
        });
    }
};
