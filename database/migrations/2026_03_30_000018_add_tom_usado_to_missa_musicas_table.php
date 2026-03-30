<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missa_musicas', function (Blueprint $table) {
            $table->string('tom_usado', 20)->nullable()->after('versao_musical_id');
        });
    }

    public function down(): void
    {
        Schema::table('missa_musicas', function (Blueprint $table) {
            $table->dropColumn('tom_usado');
        });
    }
};
