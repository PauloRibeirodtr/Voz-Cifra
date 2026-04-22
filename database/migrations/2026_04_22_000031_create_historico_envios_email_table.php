<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_envios_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('auditoria_evento_id')->nullable()->constrained('auditoria_eventos')->nullOnDelete();
            $table->string('origem_tipo', 120)->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->string('destinatario_email');
            $table->string('destinatario_nome')->nullable();
            $table->string('tipo_email', 80);
            $table->string('assunto', 255);
            $table->string('status_envio', 20)->default('pendente');
            $table->text('mensagem_retorno')->nullable();
            $table->string('mensagem_id_provedor', 120)->nullable();
            $table->string('mailer', 60)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'created_at'], 'historico_envios_email_usuario_data_index');
            $table->index(['tipo_email', 'created_at'], 'historico_envios_email_tipo_data_index');
            $table->index(['status_envio', 'created_at'], 'historico_envios_email_status_data_index');
            $table->index(['origem_tipo', 'origem_id'], 'historico_envios_email_origem_index');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE historico_envios_email
                ADD CONSTRAINT historico_envios_email_status_check
                CHECK (status_envio IN ('pendente', 'enviado', 'falhou', 'cancelado'))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE historico_envios_email DROP CONSTRAINT IF EXISTS historico_envios_email_status_check');
        }
        Schema::dropIfExists('historico_envios_email');
    }
};
