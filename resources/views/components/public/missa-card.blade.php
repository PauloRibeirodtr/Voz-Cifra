@props([
    'missa',
])

<article class="missa-card" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <p style="margin:0;color:#d2aa66;text-transform:uppercase;letter-spacing:.12em;font-size:.76rem;font-weight:800;">
                {{ $missa['igreja'] }}
            </p>
            @if (!empty($missa['igreja_localidade']))
                <p style="margin:.35rem 0 0;color:#d8c7b4;font-size:.96rem;line-height:1.6;">
                    {{ $missa['igreja_localidade'] }}
                </p>
            @endif
            <h3 style="margin:.6rem 0 0;font-family:Georgia,'Times New Roman',serif;font-size:1.62rem;line-height:1.15;">
                {{ $missa['titulo'] }}
            </h3>
        </div>

        <x-public.status-badge :status="$missa['status']" />
    </div>

    <p style="margin:0;color:#f0e4d4;font-size:1rem;line-height:1.8;">
        {{ $missa['resumo'] }}
    </p>

    <div style="display:grid;gap:.75rem;color:#ccbba7;line-height:1.8;">
        <div><strong style="color:#f5efe6;">Quando:</strong> {{ $missa['data_formatada'] }} • {{ $missa['horario'] }}</div>
        <div><strong style="color:#f5efe6;">Tempo liturgico:</strong> {{ $missa['tempo_liturgico'] }}</div>
    </div>

    <div style="margin-top:auto;display:grid;gap:.7rem;">
        <span style="color:#d2aa66;font-size:.95rem;font-weight:700;">Abrir missa publica</span>
        <x-public.button :href="$missa['url']" variant="secondary" style="width:100%;">
            Ver Missa
        </x-public.button>
    </div>
</article>
