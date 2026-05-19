<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->string('telefone_secretaria', 20)->nullable()->after('cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('igrejas', function (Blueprint $table) {
            $table->dropColumn('telefone_secretaria');
        });
    }
};
