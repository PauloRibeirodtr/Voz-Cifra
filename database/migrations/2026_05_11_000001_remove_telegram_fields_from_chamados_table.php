<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('chamados', 'solicitante_telegram_chat_id')) {
            return;
        }

        Schema::table('chamados', function (Blueprint $table): void {
            $table->dropColumn('solicitante_telegram_chat_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('chamados', 'solicitante_telegram_chat_id')) {
            return;
        }

        Schema::table('chamados', function (Blueprint $table): void {
            $table->string('solicitante_telegram_chat_id')->nullable()->after('solicitante_email');
        });
    }
};
