<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('momentos_liturgicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->integer('ordem_exibicao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ordem_exibicao');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('momentos_liturgicos');
    }
};
