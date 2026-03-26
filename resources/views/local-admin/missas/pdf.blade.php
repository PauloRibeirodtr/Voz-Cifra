<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Missa {{ $missa->titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1, h2, h3 { margin: 0; }
        .header { margin-bottom: 20px; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #d1d5db; border-radius: 12px; padding: 14px; margin-bottom: 14px; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; padding: 6px 0; }
        table.repertorio { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.repertorio th, table.repertorio td { border: 1px solid #d1d5db; padding: 10px; text-align: left; }
        table.repertorio th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $missa->igreja->nome }}</h1>
        <p class="muted">{{ $missa->igreja->cidade }} - {{ $missa->igreja->estado }}</p>
        <h2 style="margin-top: 10px;">{{ $missa->titulo }}</h2>
        <p class="muted">
            {{ optional($missa->data_missa)->format('d/m/Y') }} • {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
        </p>
    </div>

    <div class="box">
        <table class="grid">
            <tr>
                <td width="50%">
                    <strong>Tempo liturgico:</strong><br>
                    <span class="muted">{{ $missa->tempoLiturgico?->nome ?: 'Nao definido' }}</span>
                </td>
                <td width="50%">
                    <strong>Padre:</strong><br>
                    <span class="muted">{{ $missa->padre?->nome ?: 'Nao vinculado' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <h3>Repertorio</h3>
    <table class="repertorio">
        <thead>
            <tr>
                <th width="8%">Ordem</th>
                <th width="26%">Momento liturgico</th>
                <th width="26%">Musica</th>
                <th width="20%">Versao</th>
                <th width="10%">Tom</th>
                <th width="10%">BPM</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($missa->missaMusicas as $item)
                <tr>
                    <td>{{ $item->ordem }}</td>
                    <td>{{ $item->momentoLiturgico?->nome ?: '-' }}</td>
                    <td>{{ $item->musica?->titulo ?: '-' }}</td>
                    <td>{{ $item->versaoMusical?->titulo ?: 'Nao vinculada' }}</td>
                    <td>{{ $item->versaoMusical?->tom_musical ?: '-' }}</td>
                    <td>{{ $item->versaoMusical?->bpm ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Nenhum item cadastrado no repertorio desta missa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($missa->observacoes)
        <div class="box" style="margin-top: 18px;">
            <strong>Observacoes</strong>
            <p class="muted" style="margin-top: 8px; line-height: 1.6;">{{ $missa->observacoes }}</p>
        </div>
    @endif
</body>
</html>
