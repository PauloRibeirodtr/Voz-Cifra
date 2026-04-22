<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missas', function (Blueprint $table) {
            $table->index(
                ['igreja_id', 'publica_para_fieis', 'ativo', 'data_missa'],
                'missas_publica_para_fieis_lookup_index'
            );

            $table->index(
                ['igreja_id', 'publica_para_musicos', 'ativo', 'data_missa'],
                'missas_publica_para_musicos_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('missas', function (Blueprint $table) {
            $table->dropIndex('missas_publica_para_fieis_lookup_index');
            $table->dropIndex('missas_publica_para_musicos_lookup_index');
        });
    }
};
