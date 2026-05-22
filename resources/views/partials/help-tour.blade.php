@php
    use App\Enums\PapelIgreja;
    use Illuminate\Support\Facades\Route;

    $usuarioAjuda = auth()->user();
    $igrejaAtivaAjuda = $usuarioAjuda ? $usuarioAjuda->igrejaAtiva() : null;
    $igrejaAtivaIdAjuda = $igrejaAtivaAjuda ? $igrejaAtivaAjuda->id : null;
    $urlAjuda = static fn (string $routeName): ?string => Route::has($routeName) ? route($routeName) : null;

    $acoesAjuda = [];
    $adicionarAcaoAjuda = static function (string $perfil, string $titulo, string $url, string $icone, array $termos = [], ?array $guia = null) use (&$acoesAjuda): void {
        if ($url === '') {
            return;
        }

        $acoesAjuda[] = [
            'perfil' => $perfil,
            'titulo' => $titulo,
            'url' => $url,
            'icone' => $icone,
            'busca' => mb_strtolower($perfil . ' ' . $titulo . ' ' . implode(' ', $termos)),
            'guia' => $guia,
        ];
    };

    if ($usuarioAjuda && $usuarioAjuda->ehAdminMaster()) {
        $adicionarAcaoAjuda('Admin master', 'Cadastrar usuario', $urlAjuda('admin.usuarios.create') ?? '', 'fa-user-plus', ['pessoa', 'admin', 'musico', 'coordenador'], [
            'id' => 'cadastro-usuario',
            'rota' => 'admin.usuarios.create',
            'passos' => [
                ['alvo' => '[data-guide-target="usuario-tipo"]', 'foco' => '[data-tipo-cadastro]', 'titulo' => 'Escolha o perfil permitido', 'texto' => 'Admin master pode criar admin master, coordenador, admin local, musico e padre. Coordenador e admin local usam os fluxos proprios da igreja.'],
                ['alvo' => '[data-guide-target="usuario-igreja"]', 'foco' => '[data-igreja-filtro]', 'titulo' => 'Escolha a igreja inicial', 'texto' => 'Coordenador, admin local e musico precisam de igreja. Admin master nao precisa. Padre pode ter igreja, mas nao e obrigatorio.'],
                ['alvo' => '[data-guide-target="usuario-dados"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome completo', 'texto' => 'Use o nome que a equipe reconhece. Isso ajuda na busca, nos chamados e na auditoria.'],
                ['alvo' => '[data-guide-target="usuario-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita cadastro duplicado. Se a pessoa ja existir, o sistema reaproveita a conta.'],
                ['alvo' => '[data-guide-target="usuario-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail de acesso', 'texto' => 'Esse e-mail recebe o convite e a redefinicao de senha. Padre sem login pode ficar em branco.'],
                ['alvo' => '[data-guide-target="usuario-telefone"]', 'foco' => '[name="telefone"]', 'titulo' => 'Adicione o telefone', 'texto' => 'Nao e obrigatorio, mas facilita contato e suporte quando a pessoa tiver dificuldade de acesso.'],
                ['alvo' => '[data-guide-target="usuario-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso inicial', 'texto' => 'Deixe ativo para liberar a conta. Marque convite se quiser enviar o link de primeiro acesso agora.'],
                ['alvo' => '[data-guide-target="usuario-salvar"]', 'titulo' => 'Conclua o cadastro', 'texto' => 'Revise os dados e salve. Depois voce pode ajustar papeis, ativar ou reenviar convite.'],
            ],
        ]);
        $adicionarAcaoAjuda('Admin master', 'Gerenciar usuarios', $urlAjuda('admin.usuarios.index') ?? '', 'fa-users-gear', ['perfis', 'papeis', 'acesso']);
        $adicionarAcaoAjuda('Admin master', 'Cadastrar igreja', $urlAjuda('admin.igrejas.create') ?? '', 'fa-church', ['paroquia', 'comunidade']);
        $adicionarAcaoAjuda('Admin master', 'Ver chamados abertos', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento']);
        $adicionarAcaoAjuda('Admin master', 'Ver chamados encerrados', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados']);
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Admin local', 'Cadastrar musico', $urlAjuda('local-admin.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe', 'perfil'], [
            'id' => 'cadastro-musico-local',
            'rota' => 'local-admin.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do musico', 'texto' => 'Admin local cadastra apenas musicos da igreja ativa. Use o nome completo para achar a pessoa depois.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF impede duplicidade. Se a pessoa ja existe, ela e vinculada como musico desta igreja.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'O musico usa esse e-mail para acessar o painel, repertorio e cifras.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja', 'texto' => 'Neste fluxo a igreja ja vem travada na igreja ativa do admin local.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Escolha o convite', 'texto' => 'Mantenha ativo e envie o convite se o musico ja deve acessar agora.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o musico', 'texto' => 'Depois de salvar, ele aparece na equipe musical da igreja.'],
            ],
        ]);
        $adicionarAcaoAjuda('Admin local', 'Gerenciar equipe musical', $urlAjuda('local-admin.musicos.index') ?? '', 'fa-users', ['musicos', 'coordenadores']);
        $adicionarAcaoAjuda('Admin local', 'Montar uma missa', $urlAjuda('local-admin.missas.create') ?? '', 'fa-calendar-plus', ['celebracao', 'repertorio']);
        $adicionarAcaoAjuda('Admin local', 'Ver missas cadastradas', $urlAjuda('local-admin.missas.index') ?? '', 'fa-calendar-check', ['repertorio', 'publicar']);
        $adicionarAcaoAjuda('Admin local', 'Atualizar dados e links da igreja', $urlAjuda('local-admin.church') ?? '', 'fa-link', ['qr', 'publico']);
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar musico', $urlAjuda('coordenador.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe'], [
            'id' => 'cadastro-musico-coordenador',
            'rota' => 'coordenador.musicos.create',
            'passos' => [
                ['alvo' => '[data-guide-target="musico-nome"]', 'foco' => '[name="nome"]', 'titulo' => 'Digite o nome do musico', 'texto' => 'Coordenador pode cadastrar musicos da igreja ativa e tambem atribuir admin local pelo fluxo da igreja.'],
                ['alvo' => '[data-guide-target="musico-cpf"]', 'foco' => '[name="cpf"]', 'titulo' => 'Informe o CPF', 'texto' => 'O CPF evita duplicar pessoas e permite reaproveitar um usuario ja existente.'],
                ['alvo' => '[data-guide-target="musico-email"]', 'foco' => '[name="email"]', 'titulo' => 'Informe o e-mail', 'texto' => 'Esse e-mail sera usado para convite, login e recuperacao de senha.'],
                ['alvo' => '[data-guide-target="musico-igreja"]', 'titulo' => 'Confira a igreja ativa', 'texto' => 'O cadastro entra na igreja selecionada no topo do painel do coordenador.'],
                ['alvo' => '[data-guide-target="musico-acesso"]', 'foco' => '[name="enviar_convite"]', 'titulo' => 'Defina o acesso', 'texto' => 'Ativo libera o usuario. Convite envia o link de primeiro acesso com seguranca.'],
                ['alvo' => '[data-guide-target="musico-salvar"]', 'titulo' => 'Salve o cadastro', 'texto' => 'Depois de salvar, o musico fica disponivel para repertorios e rotinas da igreja.'],
            ],
        ]);
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar musica ou cifra', $urlAjuda('coordenador.musicas.create') ?? '', 'fa-music', ['biblioteca', 'versao']);
        $adicionarAcaoAjuda('Coordenador', 'Organizar momentos liturgicos', $urlAjuda('coordenador.momentos-liturgicos.index') ?? '', 'fa-list-ol', ['entrada', 'comunhao', 'final']);
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados abertos', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento']);
        $adicionarAcaoAjuda('Coordenador', 'Ver chamados encerrados', ($urlAjuda('coordenador.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados']);
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Musico', 'Ver repertorio', $urlAjuda('member.repertorio') ?? '', 'fa-list-check', ['missa', 'ensaio']);
        $adicionarAcaoAjuda('Musico', 'Consultar musicas', $urlAjuda('member.musicas.index') ?? '', 'fa-magnifying-glass', ['cifra', 'tom']);
        $adicionarAcaoAjuda('Musico', 'Meus estudos', $urlAjuda('member.colecoes.index') ?? '', 'fa-book-open-reader', ['colecao', 'favoritos']);
        $adicionarAcaoAjuda('Musico', 'Abrir chamado de suporte', $urlAjuda('member.chamados.create') ?? '', 'fa-circle-plus', ['problema', 'ajuda']);
        $adicionarAcaoAjuda('Musico', 'Acompanhar meus chamados', $urlAjuda('member.chamados.index') ?? '', 'fa-message', ['suporte', 'resposta']);
    }
@endphp

@if (count($acoesAjuda) > 0)
    <style>
        .help-actions-launcher {
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

        .help-actions-panel {
            position: fixed;
            inset: auto 1.25rem 5.5rem auto;
            z-index: 70;
            width: min(27rem, calc(100vw - 2rem));
            max-height: min(38rem, calc(100vh - 7rem));
            overflow: auto;
            border: 1px solid rgba(140, 105, 51, .18);
            border-radius: 1.25rem;
            background: #fffdf8;
            color: #1d1513;
            box-shadow: 0 24px 70px rgba(20, 10, 8, .28);
        }

        @media (max-width: 640px) {
            .help-actions-launcher {
                right: .85rem;
                bottom: .85rem;
                padding: .75rem .9rem;
            }

            .help-actions-panel {
                inset: auto .75rem 4.75rem .75rem;
                width: auto;
            }
        }
    </style>

    <button type="button" class="help-actions-launcher" data-help-open aria-haspopup="dialog" aria-controls="helpActionsPanel">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Ajuda</span>
    </button>

    <section id="helpActionsPanel" class="help-actions-panel hidden" data-help-panel aria-label="Ajuda por busca">
        <div class="sticky top-0 z-10 border-b border-[#eadfce] bg-[#fffdf8] px-5 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">Ajuda</p>
                    <h2 class="mt-1 text-lg font-black text-[#1d1513]">O que voce quer fazer?</h2>
                </div>
                <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-help-close aria-label="Fechar ajuda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="mt-4 flex items-center gap-2 rounded-2xl border border-[#eadfce] bg-white px-3 py-2">
                <i class="fa-solid fa-magnifying-glass text-[#8a5a1f]"></i>
                <input type="search" class="min-w-0 flex-1 border-0 bg-transparent text-sm text-[#1d1513] outline-none" placeholder="Buscar: missa, usuario, chamado..." data-help-search>
            </label>
        </div>

        <div class="space-y-2 p-4" data-help-list>
            @foreach ($acoesAjuda as $acaoAjuda)
                <a
                    href="{{ $acaoAjuda['url'] }}"
                    class="help-action-item flex items-center gap-3 rounded-2xl border border-[#eadfce] bg-white px-4 py-3 text-[#1d1513] transition hover:bg-[#fff7e8]"
                    data-help-item
                    data-help-search-text="{{ $acaoAjuda['busca'] }}"
                    @if (!empty($acaoAjuda['guia']))
                        data-guide-id="{{ $acaoAjuda['guia']['id'] }}"
                    @endif
                >
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#7a501f] text-white">
                        <i class="fa-solid {{ $acaoAjuda['icone'] }}"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-[0.14em] text-[#8a5a1f]">{{ $acaoAjuda['perfil'] }}</span>
                        <span class="mt-1 block text-sm font-black">{{ $acaoAjuda['titulo'] }}</span>
                    </span>
                </a>
            @endforeach

            <div class="hidden rounded-2xl border border-dashed border-[#eadfce] px-4 py-6 text-center text-sm font-semibold text-[#6d5242]" data-help-empty>
                Nenhuma acao encontrada para sua busca.
            </div>
        </div>
    </section>

    <div class="hidden fixed z-[75] rounded-2xl border-2 border-[#f59e0b] bg-transparent shadow-[0_0_0_4px_rgba(245,158,11,.18),0_18px_42px_rgba(20,10,8,.28)] transition-all before:absolute before:-right-2 before:-top-2 before:h-5 before:w-5 before:rounded-full before:border-4 before:border-white before:bg-[#f59e0b] before:shadow-lg" data-guide-highlight></div>
    <aside class="hidden fixed z-[76] w-[min(23rem,calc(100vw-2rem))] rounded-2xl border border-[#eadfce] bg-[#fffdf8] p-4 text-[#1d1513] shadow-[0_24px_70px_rgba(20,10,8,.28)]" data-guide-card aria-live="polite"></aside>

    <script>
        window.vozCifraGuias = @json(collect($acoesAjuda)->pluck('guia')->filter()->values(), JSON_UNESCAPED_UNICODE);

        document.addEventListener('DOMContentLoaded', () => {
            const painel = document.querySelector('[data-help-panel]');
            const abrir = document.querySelector('[data-help-open]');
            const fechar = document.querySelector('[data-help-close]');
            const busca = document.querySelector('[data-help-search]');
            const itens = Array.from(document.querySelectorAll('[data-help-item]'));
            const vazio = document.querySelector('[data-help-empty]');
            const guias = window.vozCifraGuias || [];
            const highlight = document.querySelector('[data-guide-highlight]');
            const card = document.querySelector('[data-guide-card]');
            const rotaAtual = '{{ Route::currentRouteName() }}';
            const storageKey = 'vozCifraGuiaPendente';
            let guiaAtual = null;
            let passoAtual = 0;

            const mostrarPainel = (mostrar) => {
                painel?.classList.toggle('hidden', !mostrar);
                if (mostrar) {
                    setTimeout(() => busca?.focus(), 50);
                }
            };

            const filtrar = () => {
                const termo = (busca?.value || '').trim().toLowerCase();
                let visiveis = 0;

                itens.forEach((item) => {
                    const combina = termo === '' || (item.dataset.helpSearchText || '').includes(termo);
                    item.classList.toggle('hidden', !combina);
                    if (combina) {
                        visiveis += 1;
                    }
                });

                vazio?.classList.toggle('hidden', visiveis > 0);
            };

            const encerrarGuia = () => {
                guiaAtual = null;
                passoAtual = 0;
                highlight?.classList.add('hidden');
                card?.classList.add('hidden');
                if (card) {
                    card.innerHTML = '';
                }
            };

            const posicionarCard = (rect) => {
                if (!card) {
                    return;
                }

                const margem = 16;
                const largura = Math.min(368, window.innerWidth - (margem * 2));
                let left = rect.right + margem;
                let top = rect.top;

                if (left + largura > window.innerWidth - margem) {
                    left = Math.max(margem, rect.left - largura - margem);
                }

                if (window.innerWidth < 768 || left < margem) {
                    left = margem;
                    top = Math.min(rect.bottom + margem, window.innerHeight - 230);
                }

                card.style.left = `${Math.max(margem, left)}px`;
                card.style.top = `${Math.max(margem, top)}px`;
                card.style.width = `${largura}px`;
            };

            const renderizarGuia = () => {
                if (!guiaAtual || !card || !highlight) {
                    return;
                }

                const passos = guiaAtual.passos || [];
                const passo = passos[passoAtual];

                if (!passo) {
                    encerrarGuia();
                    return;
                }

                const alvo = document.querySelector(passo.alvo);

                if (!alvo) {
                    passoAtual += 1;
                    renderizarGuia();
                    return;
                }

                alvo.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' });

                window.setTimeout(() => {
                    const rect = alvo.getBoundingClientRect();
                    const foco = passo.foco ? alvo.querySelector(passo.foco) || document.querySelector(passo.foco) : null;

                    if (foco && typeof foco.focus === 'function') {
                        foco.focus({ preventScroll: true });
                    }

                    highlight.classList.remove('hidden');
                    highlight.style.top = `${Math.max(8, rect.top - 8)}px`;
                    highlight.style.left = `${Math.max(8, rect.left - 8)}px`;
                    highlight.style.width = `${Math.min(window.innerWidth - 16, rect.width + 16)}px`;
                    highlight.style.height = `${Math.min(window.innerHeight - 16, rect.height + 16)}px`;

                    card.classList.remove('hidden');
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8a5a1f]">${passoAtual + 1} de ${passos.length}</p>
                                <h3 class="mt-1 text-lg font-black text-[#1d1513]">${passo.titulo}</h3>
                            </div>
                            <button type="button" class="rounded-full border border-[#eadfce] px-3 py-2 text-sm font-bold text-[#5a3a1d]" data-guide-close aria-label="Fechar guia">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-[#5f4a3d]">${passo.texto}</p>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <button type="button" class="rounded-xl border border-[#eadfce] px-3 py-2 text-sm font-black text-[#3d2a1e] disabled:opacity-40" data-guide-prev ${passoAtual === 0 ? 'disabled' : ''}>Voltar</button>
                            <button type="button" class="rounded-xl bg-[#7a501f] px-4 py-2 text-sm font-black text-white" data-guide-next>${passoAtual === passos.length - 1 ? 'Concluir' : 'Proximo'}</button>
                        </div>
                    `;

                    posicionarCard(rect);
                }, 220);
            };

            const iniciarGuia = (id) => {
                guiaAtual = guias.find((guia) => guia && guia.id === id) || null;
                passoAtual = 0;
                mostrarPainel(false);
                renderizarGuia();
            };

            abrir?.addEventListener('click', () => mostrarPainel(painel?.classList.contains('hidden')));
            fechar?.addEventListener('click', () => mostrarPainel(false));
            busca?.addEventListener('input', filtrar);

            itens.forEach((item) => {
                item.addEventListener('click', (event) => {
                    const guideId = item.dataset.guideId;
                    if (!guideId) {
                        return;
                    }

                    const guia = guias.find((registro) => registro && registro.id === guideId);

                    if (guia && guia.rota === rotaAtual) {
                        event.preventDefault();
                        iniciarGuia(guideId);
                        return;
                    }

                    if (guia) {
                        sessionStorage.setItem(storageKey, guideId);
                    }
                });
            });

            const guiaPendente = sessionStorage.getItem(storageKey);
            if (guiaPendente) {
                const guia = guias.find((registro) => registro && registro.id === guiaPendente);
                if (guia && guia.rota === rotaAtual) {
                    sessionStorage.removeItem(storageKey);
                    window.setTimeout(() => iniciarGuia(guiaPendente), 350);
                }
            }

            document.addEventListener('click', (event) => {
                if (event.target.closest('[data-guide-close]')) {
                    encerrarGuia();
                    return;
                }

                if (event.target.closest('[data-guide-next]')) {
                    if (guiaAtual && passoAtual < (guiaAtual.passos || []).length - 1) {
                        passoAtual += 1;
                        renderizarGuia();
                    } else {
                        encerrarGuia();
                    }
                    return;
                }

                if (event.target.closest('[data-guide-prev]') && passoAtual > 0) {
                    passoAtual -= 1;
                    renderizarGuia();
                }
            });

            window.addEventListener('resize', () => {
                if (guiaAtual) {
                    renderizarGuia();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    mostrarPainel(false);
                    encerrarGuia();
                }
            });
        });
    </script>
@endif
