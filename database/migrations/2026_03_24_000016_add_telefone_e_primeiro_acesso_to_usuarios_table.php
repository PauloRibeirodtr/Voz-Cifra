<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('telefone', 20)->nullable()->after('email');
            $table->boolean('primeiro_acesso')->default(false)->after('ativo');
            $table->index('primeiro_acesso');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex(['primeiro_acesso']);
            $table->dropColumn(['telefone', 'primeiro_acesso']);
        });
    }
};
