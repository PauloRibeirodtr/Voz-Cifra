<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
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

        DB::statement("
            ALTER TABLE feedbacks
            ADD CONSTRAINT feedbacks_tipo_check
            CHECK (tipo IN ('critica', 'sugestao', 'ajuste', 'elogio'))
        ");

        DB::statement("
            ALTER TABLE feedbacks
            ADD CONSTRAINT feedbacks_status_check
            CHECK (status IN ('novo', 'lido', 'arquivado'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE feedbacks DROP CONSTRAINT IF EXISTS feedbacks_status_check');
        DB::statement('ALTER TABLE feedbacks DROP CONSTRAINT IF EXISTS feedbacks_tipo_check');
        Schema::dropIfExists('feedbacks');
    }
};
