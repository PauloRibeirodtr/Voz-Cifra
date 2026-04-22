<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('igreja_id')->nullable()->constrained('igrejas')->restrictOnDelete();
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->string('email')->unique();
            $table->string('telefone', 20)->nullable();
            $table->string('password');
            $table->string('perfil_global', 20)->default('usuario');
            $table->smallInteger('nivel_global')->default(1);
            $table->boolean('eh_padre')->default(false);
            $table->boolean('ativo')->default(true);
            $table->boolean('primeiro_acesso')->default(false);
            $table->string('theme_preference', 16)->default('system');
            $table->rememberToken();
            $table->timestamps();

            $table->index('perfil_global');
            $table->index('nivel_global');
            $table->index('ativo');
            $table->index('primeiro_acesso');
            $table->index('eh_padre');
            $table->index(['igreja_id', 'perfil_global']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                ALTER TABLE usuarios
                ADD CONSTRAINT usuarios_perfil_global_check
                CHECK (perfil_global IN ('admin_master', 'usuario'))
            ");

            DB::statement("
                ALTER TABLE usuarios
                ADD CONSTRAINT usuarios_nivel_global_check
                CHECK (nivel_global BETWEEN 1 AND 6)
            ");
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_nivel_global_check');
            DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_perfil_global_check');
        }
        Schema::dropIfExists('usuarios');
    }
};
