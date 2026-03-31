<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Missa extends Model
{
    use HasFactory;

    protected $table = 'missas';

    protected $fillable = [
        'igreja_id',
        'padre_id',
        'tempo_liturgico_id',
        'titulo',
        'data_missa',
        'hora_inicio',
        'hora_fim',
        'observacoes',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_missa' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Padre::class, 'padre_id');
    }

    public function tempoLiturgico(): BelongsTo
    {
        return $this->belongsTo(TempoLiturgico::class, 'tempo_liturgico_id');
    }

    public function missaMusicas(): HasMany
    {
        return $this->hasMany(MissaMusica::class, 'missa_id');
    }

    public function atravessaMadrugada(): bool
    {
        return $this->normalizarHorario($this->hora_fim) <= $this->normalizarHorario($this->hora_inicio);
    }

    public function dataHoraInicio(string $timezone = 'America/Cuiaba'): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $this->data_missa->format('Y-m-d') . ' ' . $this->normalizarHorario($this->hora_inicio),
            $timezone
        );
    }

    public function dataHoraFim(string $timezone = 'America/Cuiaba'): CarbonImmutable
    {
        $dataHoraFim = CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $this->data_missa->format('Y-m-d') . ' ' . $this->normalizarHorario($this->hora_fim),
            $timezone
        );

        if ($this->atravessaMadrugada()) {
            return $dataHoraFim->addDay();
        }

        return $dataHoraFim;
    }

    private function normalizarHorario(mixed $horario): string
    {
        $horario = substr((string) $horario, 0, 8);

        return strlen($horario) === 5 ? $horario . ':00' : $horario;
    }
}
