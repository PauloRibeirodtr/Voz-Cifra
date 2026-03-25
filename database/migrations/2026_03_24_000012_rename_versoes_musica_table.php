<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('versoes_musica', 'versoes_musicais');
    }

    public function down(): void
    {
        Schema::rename('versoes_musicais', 'versoes_musica');
    }
};
