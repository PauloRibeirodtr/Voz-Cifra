<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramVinculo extends Model
{
    use HasFactory;

    protected $table = 'telegram_vinculos';

    protected $fillable = [
        'usuario_id',
        'chat_id',
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'idioma',
        'token_vinculo',
        'token_expira_em',
        'vinculado_em',
        'ultimo_acesso_em',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'token_expira_em' => 'datetime',
            'vinculado_em' => 'datetime',
            'ultimo_acesso_em' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
