@php
    use App\Enums\PapelIgreja;
    use Illuminate\Support\Facades\Route;

    $usuarioAjuda = auth()->user();
    $igrejaAtivaAjuda = $usuarioAjuda?->igrejaAtiva();
    $igrejaAtivaIdAjuda = $igrejaAtivaAjuda?->id;
    $urlAjuda = static fn (string $routeName): ?string => Route::has($routeName) ? route($routeName) : null;

    $tutoriaisAjuda = [];

    if ($usuarioAjuda?->ehAdminMaster()) {
        $tutoriaisAjuda[] = [
            'id' => 'admin-master',
            'perfil' => 'Admin master',
            'titulo' => 'Administrar o sistema',
            'descricao' => 'Use este guia para localizar igrejas, usuarios e chamados sem se perder no painel central.',
            'url' => $urlAjuda('admin.dashboard'),
            'passos' => [
                ['alvo' => '#mainContent', 'titulo' => 'Painel central', 'texto' => 'Aqui ficam os indicadores e atalhos principais da administracao geral.'],
                ['alvo' => 'a[href*="/admin/igrejas"]', 'titulo' => 'Igrejas', 'texto' => 'Cadastre, revise dados e acompanhe se cada igreja ja tem admin local.'],
                ['alvo' => 'a[href*="/admin/usuarios"]', 'titulo' => 'Usuarios', 'texto' => 'Crie contas, vincule papeis e mantenha acessos corretos por igreja.'],
                ['alvo' => 'a[href*="/admin/chamados"]', 'titulo' => 'Chamados', 'texto' => 'Acompanhe pedidos de suporte e solicitacoes que precisam de permissao maior.'],
            ],
        ];
    }

    if ($usuarioAjuda?->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdAjuda)) {
        $tutoriaisAjuda[] = [
            'id' => 'admin-local',
            'perfil' => 'Admin local',
            'titulo' => 'Cuidar da igreja',
            'descricao' => 'Guia rapido para atualizar dados, montar missas e gerenciar a equipe musical.',
            'url' => $urlAjuda('local-admin.dashboard'),
            'passos' => [
                ['alvo' => '#mainContent', 'titulo' => 'Resumo da igreja', 'texto' => 'Comece por aqui para conferir a situacao da igreja ativa.'],
                ['alvo' => 'a[href*="/igreja/dados"]', 'titulo' => 'Dados e links', 'texto' => 'Revise endereco, telefone e links publicos antes de divulgar.'],
                ['alvo' => 'a[href*="/igreja/missas"]', 'titulo' => 'Missas', 'texto' => 'Crie a celebracao, monte o repertorio e revise como fiel e musico vao enxergar.'],
                ['alvo' => 'a[href*="/igreja/musicos"]', 'titulo' => 'Equipe musical', 'texto' => 'Cadastre musicos e coordenadores sem expor funcoes que nao pertencem a eles.'],
            ],
        ];
    }

    if ($usuarioAjuda?->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdAjuda)) {
        $tutoriaisAjuda[] = [
            'id' => 'coordenador',
            'perfil' => 'Coordenador',
            'titulo' => 'Organizar repertorio',
            'descricao' => 'Mostra onde cadastrar musicas, cifras, momentos liturgicos e acompanhar chamados da equipe.',
            'url' => $urlAjuda('coordenador.dashboard'),
            'passos' => [
                ['alvo' => '#mainContent', 'titulo' => 'Coordenacao musical', 'texto' => 'Use esta area para manter o acervo pronto para as celebracoes.'],
                ['alvo' => 'a[href*="/coordenacao/musicas"]', 'titulo' => 'Cadastrar cifras', 'texto' => 'Busque antes de cadastrar, revise duplicidades e crie versoes quando faltar cifra.'],
                ['alvo' => 'a[href*="/coordenacao/momentos-liturgicos"]', 'titulo' => 'Momentos liturgicos', 'texto' => 'Mantenha a ordem dos momentos para o sistema organizar a missa automaticamente.'],
                ['alvo' => 'a[href*="/coordenacao/chamados"]', 'titulo' => 'Chamados', 'texto' => 'Acompanhe pedidos que chegam da equipe e ajude quem estiver travado.'],
            ],
        ];
    }

    if ($usuarioAjuda?->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdAjuda)) {
        $tutoriaisAjuda[] = [
            'id' => 'musico',
            'perfil' => 'Musico',
            'titulo' => 'Estudar e tocar',
            'descricao' => 'Guia simples para achar repertorio, abrir cifras e pedir suporte quando precisar.',
            'url' => $urlAjuda('member.dashboard'),
            'passos' => [
                ['alvo' => '#mainContent', 'titulo' => 'Painel musical', 'texto' => 'Aqui voce encontra os atalhos para estudar e acompanhar a igreja ativa.'],
                ['alvo' => 'a[href*="/musico/repertorio"]', 'titulo' => 'Repertorio', 'texto' => 'Veja as musicas publicadas para ensaio e celebracao.'],
                ['alvo' => 'a[href*="/musico/musicas"]', 'titulo' => 'Consultar musicas', 'texto' => 'Pesquise cifras, altere tom na leitura e salve musicas para estudo.'],
                ['alvo' => 'a[href*="/musico/chamados"]', 'titulo' => 'Suporte', 'texto' => 'Abra um chamado quando nao encontrar uma informacao ou precisar corrigir acesso.'],
            ],
        ];
    }
@endphp

@if (count($tutoriaisAjuda) > 0)
    <style>
        .help-tour-launcher {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 60;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border: 1px solid rgba(214, 173, 108, .45);
            border-radius: 999px;
            background: #1f1514;
            color: #fff8ed;
            padding: .8rem 1rem;
            font-weight: 800;
            box-shadow: 0 18px 40px rgba(20, 10, 8, .28);
        }

        .help-tour-panel {
            position: fixed;
            inset: auto 1.25rem 5.5rem auto;
            z-index: 70;
            width: min(24rem, calc(100vw - 2rem));
            max-height: min(38rem, calc(100vh - 7rem));
            overflow: auto;
            border: 1px solid rgba(140, 105, 51, .18);
            border-radius: 1.25rem;
            background: #fffdf8;
            color: #1d1513;
            box-shadow: 0 24px 70px rgba(20, 10, 8, .28);
        }

        .help-tour-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(15, 10, 9, .56);
            backdrop-filter: blur(2px);
        }

        .help-tour-highlight {
            position: fixed;
            z-index: 81;
            pointer-events: none;
            border: 3px solid #f59e0b;
            border-radius: 1rem;
            box-shadow: 0 0 0 9999px rgba(15, 10, 9, .52), 0 18px 45px rgba(0, 0, 0, .22);
            transition: top .18s ease, left .18s ease, width .18s ease, height .18s ease;
        }

        .help-tour-tooltip {
            position: fixed;
            z-index: 82;
            width: min(22rem, calc(100vw - 2rem));
            border-radius: 1rem;
            background: #fffdf8;
            color: #1d1513;
            padding: 1rem;
            box-shadow: 0 24px 70px rgba(20, 10, 8, .32);
        }

        @media (max-width: 640px) {
            .help-tour-launcher {
                right: .85rem;
                bottom: .85rem;
                padding: .75rem .9rem;
            }

            .help-tour-panel {
                inset: auto .75rem 4.75rem .75rem;
                width: auto;
            }
        }
    </style>

    <button type="button" class="help-tour-launcher" data-help-open aria-haspopup="dialog" aria-controls="helpTourPanel">
        <i class="fa-solid fa-circle-question"></i>
        <span>Ajuda</span>
    </button>

    <section id="helpTourPanel" class="help-tour-panel hidden" data-help-panel aria-label="Ajuda guiada">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-[#eadfce] bg-[#fffdf8] px-5 py-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">Ajuda guiada</p>
                <h2 class="mt-1 text-lg font-black text-[#1d1513]">O que voce quer fazer?</h2>
            </div>
            <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-help-close aria-label="Fechar ajuda">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-3 p-4">
            @foreach ($tutoriaisAjuda as $tutorialAjuda)
                <article class="rounded-2xl border border-[#eadfce] bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex rounded-full bg-[#eef8ef] px-3 py-1 text-xs font-black text-[#007f4e]">{{ $tutorialAjuda['perfil'] }}</span>
                            <h3 class="mt-3 text-base font-black text-[#1d1513]">{{ $tutorialAjuda['titulo'] }}</h3>
                            <p class="mt-1 text-sm leading-6 text-[#6d5242]">{{ $tutorialAjuda['descricao'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#7a501f] px-4 py-3 text-sm font-black text-white transition hover:bg-[#5f3b17]" data-help-start="{{ $tutorialAjuda['id'] }}">
                            <i class="fa-solid fa-play"></i>
                            Comecar
                        </button>
                        @if ($tutorialAjuda['url'])
                            <a href="{{ $tutorialAjuda['url'] }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#eadfce] px-4 py-3 text-sm font-black text-[#3d2a1e] transition hover:bg-[#fff7e8]">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                Abrir area
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="help-tour-backdrop hidden" data-help-backdrop></div>
    <div class="help-tour-highlight hidden" data-help-highlight></div>
    <aside class="help-tour-tooltip hidden" data-help-tooltip role="dialog" aria-live="polite"></aside>

    <script>
        window.vozCifraTutoriais = @json($tutoriaisAjuda);

        document.addEventListener('DOMContentLoaded', () => {
            const tutoriais = window.vozCifraTutoriais || [];
            const painel = document.querySelector('[data-help-panel]');
            const abrir = document.querySelector('[data-help-open]');
            const fechar = document.querySelector('[data-help-close]');
            const backdrop = document.querySelector('[data-help-backdrop]');
            const highlight = document.querySelector('[data-help-highlight]');
            const tooltip = document.querySelector('[data-help-tooltip]');

            let tutorialAtual = null;
            let passoAtual = 0;

            const togglePainel = (mostrar) => {
                painel?.classList.toggle('hidden', !mostrar);
            };

            const encerrarTutorial = () => {
                tutorialAtual = null;
                passoAtual = 0;
                backdrop?.classList.add('hidden');
                highlight?.classList.add('hidden');
                tooltip?.classList.add('hidden');
                if (tooltip) {
                    tooltip.innerHTML = '';
                }
            };

            const posicionarTooltip = (rect) => {
                const margem = 16;
                const tooltipWidth = Math.min(352, window.innerWidth - (margem * 2));
                let left = rect ? rect.right + margem : (window.innerWidth - tooltipWidth) / 2;
                let top = rect ? rect.top : window.innerHeight / 2 - 120;

                if (left + tooltipWidth > window.innerWidth - margem) {
                    left = Math.max(margem, (rect ? rect.left : window.innerWidth / 2) - tooltipWidth - margem);
                }

                if (window.innerWidth < 768 || left < margem) {
                    left = margem;
                    top = rect ? Math.min(rect.bottom + margem, window.innerHeight - 240) : top;
                }

                tooltip.style.left = `${Math.max(margem, left)}px`;
                tooltip.style.top = `${Math.max(margem, top)}px`;
                tooltip.style.width = `${tooltipWidth}px`;
            };

            const renderizarPasso = () => {
                if (!tutorialAtual) {
                    return;
                }

                const passos = tutorialAtual.passos || [];
                const passo = passos[passoAtual];

                if (!passo) {
                    encerrarTutorial();
                    return;
                }

                backdrop?.classList.remove('hidden');
                tooltip?.classList.remove('hidden');

                const alvo = passo.alvo ? document.querySelector(passo.alvo) : null;
                let rect = null;

                if (alvo) {
                    alvo.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'auto' });
                    rect = alvo.getBoundingClientRect();
                    highlight?.classList.remove('hidden');
                    highlight.style.top = `${Math.max(8, rect.top - 8)}px`;
                    highlight.style.left = `${Math.max(8, rect.left - 8)}px`;
                    highlight.style.width = `${Math.min(window.innerWidth - 16, rect.width + 16)}px`;
                    highlight.style.height = `${Math.min(window.innerHeight - 16, rect.height + 16)}px`;
                } else {
                    highlight?.classList.add('hidden');
                }

                tooltip.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">${tutorialAtual.perfil}</p>
                            <h3 class="mt-1 text-lg font-black text-[#1d1513]">${passo.titulo}</h3>
                        </div>
                        <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-help-finish aria-label="Fechar tutorial">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-[#5f4a3d]">${passo.texto}</p>
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span class="text-xs font-black text-[#8a5a1f]">${passoAtual + 1} de ${passos.length}</span>
                        <div class="flex gap-2">
                            <button type="button" class="rounded-xl border border-[#eadfce] px-3 py-2 text-sm font-black text-[#3d2a1e] disabled:opacity-40" data-help-prev ${passoAtual === 0 ? 'disabled' : ''}>Voltar</button>
                            <button type="button" class="rounded-xl bg-[#7a501f] px-3 py-2 text-sm font-black text-white" data-help-next>${passoAtual === passos.length - 1 ? 'Concluir' : 'Proximo'}</button>
                        </div>
                    </div>
                `;

                posicionarTooltip(rect);
            };

            abrir?.addEventListener('click', () => togglePainel(painel?.classList.contains('hidden')));
            fechar?.addEventListener('click', () => togglePainel(false));
            backdrop?.addEventListener('click', encerrarTutorial);

            document.addEventListener('click', (event) => {
                const start = event.target.closest('[data-help-start]');
                if (start) {
                    tutorialAtual = tutoriais.find((tutorial) => tutorial.id === start.dataset.helpStart);
                    passoAtual = 0;
                    togglePainel(false);
                    renderizarPasso();
                    return;
                }

                if (event.target.closest('[data-help-finish]')) {
                    encerrarTutorial();
                    return;
                }

                if (event.target.closest('[data-help-next]')) {
                    if (tutorialAtual && passoAtual < tutorialAtual.passos.length - 1) {
                        passoAtual += 1;
                        renderizarPasso();
                    } else {
                        encerrarTutorial();
                    }
                    return;
                }

                if (event.target.closest('[data-help-prev]') && passoAtual > 0) {
                    passoAtual -= 1;
                    renderizarPasso();
                }
            });

            window.addEventListener('resize', () => {
                if (tutorialAtual) {
                    renderizarPasso();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    encerrarTutorial();
                    togglePainel(false);
                }
            });
        });
    </script>
@endif
