@php
    $rotaPublicaAjuda = \Illuminate\Support\Facades\Route::currentRouteName();
    $modoPublicoAjuda = $modoPublico ?? null;

    $guiasPublicos = [];

    if ($rotaPublicaAjuda === 'root') {
        $guiasPublicos[] = [
            'id' => 'publico-inicio',
            'titulo' => 'Encontrar uma igreja',
            'descricao' => 'Mostra como buscar a comunidade e abrir a missa publicada.',
            'passos' => [
                ['alvo' => '#inicio', 'titulo' => 'Pagina inicial', 'texto' => 'Comece por aqui para entender que esta tela e publica e nao altera nada no sistema.'],
                ['alvo' => '#igrejas', 'titulo' => 'Lista de igrejas', 'texto' => 'Desca ate esta area para buscar sua comunidade pelo nome da igreja ou pela cidade.'],
                ['alvo' => '[data-church-search]', 'titulo' => 'Campo de busca', 'texto' => 'Digite poucas palavras, como cidade, bairro ou nome da igreja. As sugestoes ajudam a escolher mais rapido.'],
                ['alvo' => '[data-church-card]', 'titulo' => 'Abrir igreja', 'texto' => 'Clique no cartao da igreja para ver horarios, celebracoes e links publicos.'],
            ],
        ];
    }

    if (isset($igreja) && in_array($rotaPublicaAjuda, ['igrejas.public.show', 'igrejas.public.musicos.show'], true)) {
        $guiasPublicos[] = [
            'id' => 'publico-igreja',
            'titulo' => $modoPublicoAjuda === 'musicos' ? 'Ver cifras da missa' : 'Acompanhar missa publica',
            'descricao' => $modoPublicoAjuda === 'musicos'
                ? 'Mostra como escolher a celebracao, abrir o repertorio e ajustar a leitura.'
                : 'Mostra como abrir a celebracao e acompanhar a liturgia publicada.',
            'passos' => [
                ['alvo' => '.brand', 'titulo' => 'Igreja aberta', 'texto' => 'Confira se esta e a igreja certa antes de seguir para a celebracao.'],
                ['alvo' => '[data-schedule-carousel], .celebration-list', 'titulo' => 'Escolha a celebracao', 'texto' => 'Toque em uma missa publicada para abrir o conteudo da celebracao.'],
                ['alvo' => '#celebracao-publica', 'titulo' => $modoPublicoAjuda === 'musicos' ? 'Repertorio e cifras' : 'Celebracao', 'texto' => $modoPublicoAjuda === 'musicos' ? 'Aqui aparecem as musicas e cifras liberadas para ensaio.' : 'Aqui aparece o roteiro da celebracao para leitura dos fieis.'],
                ['alvo' => '.history-toggle', 'titulo' => 'Historico', 'texto' => 'Use o historico para encontrar missas anteriores por data, dia ou nome.'],
            ],
        ];
    }
@endphp

@if (count($guiasPublicos) > 0)
    <style>
        .public-help-launcher {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 70;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid rgba(242, 201, 125, .36);
            border-radius: 999px;
            background: rgba(18, 10, 10, .96);
            color: #fff8ed;
            padding: .85rem 1rem;
            font-weight: 900;
            box-shadow: 0 18px 42px rgba(0, 0, 0, .34);
        }

        .public-help-panel,
        .public-help-card {
            position: fixed;
            z-index: 75;
            border: 1px solid rgba(242, 201, 125, .26);
            border-radius: 1.25rem;
            background: #fff8ed;
            color: #1b1110;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .36);
        }

        .public-help-panel {
            right: 1rem;
            bottom: 5rem;
            width: min(24rem, calc(100vw - 2rem));
            overflow: hidden;
        }

        .public-help-highlight {
            position: fixed;
            z-index: 74;
            pointer-events: none;
            border: 3px solid #f2c97d;
            border-radius: 1rem;
            box-shadow: 0 0 0 9999px rgba(9, 5, 5, .62), 0 18px 44px rgba(0, 0, 0, .32);
        }

        .public-help-card {
            width: min(23rem, calc(100vw - 2rem));
            padding: 1rem;
        }
    </style>

    <button type="button" class="public-help-launcher" data-public-help-open>
        <span aria-hidden="true">?</span>
        <span>Ajuda</span>
    </button>

    <section class="public-help-panel" data-public-help-panel hidden aria-label="Ajuda da pagina publica">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:1rem;border-bottom:1px solid rgba(91,57,31,.16);">
            <div>
                <p style="margin:0;color:#8a5a1f;font-size:.72rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase;">Ajuda publica</p>
                <h2 style="margin:.25rem 0 0;font-size:1.15rem;">O que voce quer fazer?</h2>
            </div>
            <button type="button" data-public-help-close style="border:1px solid rgba(91,57,31,.16);border-radius:999px;background:white;padding:.5rem .7rem;font-weight:900;">x</button>
        </div>

        <div style="display:grid;gap:.65rem;padding:1rem;">
            @foreach ($guiasPublicos as $guiaPublico)
                <button type="button" data-public-help-start="{{ $guiaPublico['id'] }}" style="display:grid;gap:.25rem;text-align:left;border:1px solid rgba(91,57,31,.16);border-radius:1rem;background:white;padding:.9rem;color:#1b1110;">
                    <strong>{{ $guiaPublico['titulo'] }}</strong>
                    <span style="color:#6d5242;font-size:.9rem;line-height:1.5;">{{ $guiaPublico['descricao'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <div class="public-help-highlight" data-public-help-highlight hidden></div>
    <aside class="public-help-card" data-public-help-card hidden></aside>

    <script>
        window.vozCifraGuiasPublicos = @json($guiasPublicos, JSON_UNESCAPED_UNICODE);

        document.addEventListener('DOMContentLoaded', () => {
            const guias = window.vozCifraGuiasPublicos || [];
            const painel = document.querySelector('[data-public-help-panel]');
            const abrir = document.querySelector('[data-public-help-open]');
            const fechar = document.querySelector('[data-public-help-close]');
            const highlight = document.querySelector('[data-public-help-highlight]');
            const card = document.querySelector('[data-public-help-card]');
            let guiaAtual = null;
            let passoAtual = 0;

            const encerrar = () => {
                guiaAtual = null;
                passoAtual = 0;
                highlight.hidden = true;
                card.hidden = true;
                card.innerHTML = '';
            };

            const renderizar = () => {
                const passo = guiaAtual?.passos?.[passoAtual];
                if (!passo) {
                    encerrar();
                    return;
                }

                const alvo = document.querySelector(passo.alvo);
                if (!alvo) {
                    passoAtual += 1;
                    renderizar();
                    return;
                }

                alvo.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });

                window.setTimeout(() => {
                    const rect = alvo.getBoundingClientRect();
                    highlight.hidden = false;
                    highlight.style.top = `${Math.max(8, rect.top - 8)}px`;
                    highlight.style.left = `${Math.max(8, rect.left - 8)}px`;
                    highlight.style.width = `${Math.min(window.innerWidth - 16, rect.width + 16)}px`;
                    highlight.style.height = `${Math.min(window.innerHeight - 16, rect.height + 16)}px`;

                    const largura = Math.min(368, window.innerWidth - 32);
                    let left = rect.right + 16;
                    let top = rect.top;
                    if (left + largura > window.innerWidth - 16) {
                        left = Math.max(16, rect.left - largura - 16);
                    }
                    if (window.innerWidth < 768 || left < 16) {
                        left = 16;
                        top = Math.min(rect.bottom + 16, window.innerHeight - 220);
                    }

                    card.hidden = false;
                    card.style.left = `${left}px`;
                    card.style.top = `${Math.max(16, top)}px`;
                    card.style.width = `${largura}px`;
                    card.innerHTML = `
                        <p style="margin:0;color:#8a5a1f;font-size:.72rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase;">${passoAtual + 1} de ${guiaAtual.passos.length}</p>
                        <h3 style="margin:.35rem 0 0;font-size:1.12rem;">${passo.titulo}</h3>
                        <p style="margin:.65rem 0 0;color:#5f4a3d;line-height:1.6;">${passo.texto}</p>
                        <div style="display:flex;justify-content:space-between;gap:.5rem;margin-top:1rem;">
                            <button type="button" data-public-help-prev ${passoAtual === 0 ? 'disabled' : ''} style="border:1px solid rgba(91,57,31,.18);border-radius:.8rem;background:white;padding:.65rem .8rem;font-weight:900;">Voltar</button>
                            <button type="button" data-public-help-next style="border:0;border-radius:.8rem;background:#7a501f;color:white;padding:.65rem .9rem;font-weight:900;">${passoAtual === guiaAtual.passos.length - 1 ? 'Concluir' : 'Proximo'}</button>
                        </div>
                    `;
                }, 220);
            };

            abrir?.addEventListener('click', () => {
                painel.hidden = !painel.hidden;
            });

            fechar?.addEventListener('click', () => {
                painel.hidden = true;
                encerrar();
            });

            document.addEventListener('click', (event) => {
                const inicio = event.target.closest('[data-public-help-start]');
                if (inicio) {
                    guiaAtual = guias.find((guia) => guia.id === inicio.dataset.publicHelpStart);
                    passoAtual = 0;
                    painel.hidden = true;
                    renderizar();
                    return;
                }

                if (event.target.closest('[data-public-help-next]')) {
                    if (guiaAtual && passoAtual < guiaAtual.passos.length - 1) {
                        passoAtual += 1;
                        renderizar();
                    } else {
                        encerrar();
                    }
                    return;
                }

                if (event.target.closest('[data-public-help-prev]') && passoAtual > 0) {
                    passoAtual -= 1;
                    renderizar();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    painel.hidden = true;
                    encerrar();
                }
            });
        });
    </script>
@endif
