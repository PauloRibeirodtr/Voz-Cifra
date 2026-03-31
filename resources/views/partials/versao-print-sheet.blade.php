<style>
    .folha-versao { color: #0f172a; }
    .folha-topo { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 1.5rem; }
    .folha-etiqueta { display: inline-flex; align-items: center; border-radius: 9999px; background: #dcfce7; color: #166534; font-size: 11px; font-weight: 800; letter-spacing: .12em; padding: .35rem .8rem; text-transform: uppercase; }
    .folha-titulo { margin: .8rem 0 .35rem; font-size: 1.9rem; font-weight: 900; line-height: 1.1; }
    .folha-subtitulo { margin: 0; color: #475569; font-size: 1rem; }
    .folha-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .85rem; margin-bottom: 1.5rem; }
    .folha-meta-card { border: 1px solid #e2e8f0; border-radius: 1rem; background: #f8fafc; padding: .9rem 1rem; }
    .folha-meta-label { display: block; color: #64748b; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .folha-meta-value { display: block; margin-top: .35rem; color: #0f172a; font-size: 1rem; font-weight: 700; }
    .folha-cifra-wrap { border: 1px solid #e2e8f0; border-radius: 1.4rem; background: #fff; padding: 1.25rem 1.4rem; }
    .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.15rem; margin-bottom: 0.45rem; }
    .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.45rem; }
    .cifra-acordes { min-height: 1rem; margin-bottom: 0.02rem; color: #c2410c; font-weight: 800; font-size: .9rem; line-height: 1rem; letter-spacing: .01em; white-space: pre; }
    .cifra-acorde { display: inline-block; padding: 0 0.05rem; }
    .cifra-letra { color: #111827; font-size: 1.02rem; line-height: 1.65rem; white-space: pre-wrap; }
    .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #e2e8f0; color: #334155; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.38rem 0.78rem; margin: .9rem 0 .65rem; }
    .cifra-espaco { height: .85rem; }
    .folha-acordes { margin-top: 1.8rem; page-break-inside: avoid; }
    .folha-acordes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    .folha-acorde-card { border: 1px solid #e2e8f0; border-radius: 1.15rem; background: #fff; padding: .95rem; break-inside: avoid; }
    .folha-acorde-titulo { margin: 0 0 .25rem; font-size: 1rem; font-weight: 900; color: #0f172a; }
    .folha-acorde-descricao { margin: 0 0 .65rem; font-size: .82rem; color: #64748b; min-height: 1.1rem; }
    .folha-acorde-svg { display: flex; justify-content: center; }
    .folha-acorde-svg svg { width: 100%; height: auto; max-width: 170px; }
    .folha-rodape { margin-top: 1.6rem; color: #64748b; font-size: .78rem; text-align: right; }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
        .print-shell { padding: 0 !important; margin: 0 !important; }
        .folha-cifra-wrap, .folha-acorde-card, .folha-meta-card { break-inside: avoid; }
    }
</style>

<section class="folha-versao">
    <div class="folha-topo">
        <div>
            <span class="folha-etiqueta">{{ $etiquetaFolha ?? 'Folha de estudo' }}</span>
            <h1 class="folha-titulo">{{ $folha['titulo'] }}</h1>
            <p class="folha-subtitulo">{{ $folha['subtitulo'] }}</p>
        </div>
    </div>

    <div class="folha-meta-grid">
        <div class="folha-meta-card">
            <span class="folha-meta-label">Tom exibido</span>
            <span class="folha-meta-value">{{ $folha['tom_exibicao'] ?: 'Nao informado' }}</span>
        </div>
        <div class="folha-meta-card">
            <span class="folha-meta-label">Tom original</span>
            <span class="folha-meta-value">{{ $folha['tom_original'] ?: 'Nao informado' }}</span>
        </div>
        <div class="folha-meta-card">
            <span class="folha-meta-label">BPM</span>
            <span class="folha-meta-value">{{ $folha['bpm'] ?: 'Nao informado' }}</span>
        </div>
        @foreach (($folha['meta'] ?? []) as $rotulo => $valor)
            <div class="folha-meta-card">
                <span class="folha-meta-label">{{ $rotulo }}</span>
                <span class="folha-meta-value">{{ $valor ?: 'Nao informado' }}</span>
            </div>
        @endforeach
    </div>

    <div class="folha-cifra-wrap">
        {!! $folha['html_cifra'] !!}
    </div>

    @if (!empty($folha['acordes']))
        <section class="folha-acordes">
            <h2 style="margin:0 0 .9rem;font-size:1.15rem;font-weight:900;color:#0f172a;">Acordes usados nesta folha</h2>
            <div class="folha-acordes-grid">
                @foreach ($folha['acordes'] as $acorde)
                    <article class="folha-acorde-card">
                        <h3 class="folha-acorde-titulo">{{ $acorde['nome'] }}</h3>
                        <p class="folha-acorde-descricao">{{ $acorde['descricao'] ?: 'Shape salvo na biblioteca.' }}</p>
                        <div class="folha-acorde-svg">{!! $acorde['svg'] !!}</div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <p class="folha-rodape">Gerado por Voz &amp; Cifra</p>
</section>
