@php
    use App\Enums\PapelIgreja;
    use Illuminate\Support\Facades\Route;

    $usuarioAjuda = auth()->user();
    $igrejaAtivaAjuda = $usuarioAjuda ? $usuarioAjuda->igrejaAtiva() : null;
    $igrejaAtivaIdAjuda = $igrejaAtivaAjuda ? $igrejaAtivaAjuda->id : null;
    $urlAjuda = static fn (string $routeName): ?string => Route::has($routeName) ? route($routeName) : null;

    $acoesAjuda = [];
    $adicionarAcaoAjuda = static function (string $perfil, string $titulo, string $url, string $icone, array $termos = []) use (&$acoesAjuda): void {
        if ($url === '') {
            return;
        }

        $acoesAjuda[] = [
            'perfil' => $perfil,
            'titulo' => $titulo,
            'url' => $url,
            'icone' => $icone,
            'busca' => mb_strtolower($perfil . ' ' . $titulo . ' ' . implode(' ', $termos)),
        ];
    };

    if ($usuarioAjuda && $usuarioAjuda->ehAdminMaster()) {
        $adicionarAcaoAjuda('Admin master', 'Cadastrar usuario', $urlAjuda('admin.usuarios.create') ?? '', 'fa-user-plus', ['pessoa', 'admin', 'musico', 'coordenador']);
        $adicionarAcaoAjuda('Admin master', 'Gerenciar usuarios', $urlAjuda('admin.usuarios.index') ?? '', 'fa-users-gear', ['perfis', 'papeis', 'acesso']);
        $adicionarAcaoAjuda('Admin master', 'Cadastrar igreja', $urlAjuda('admin.igrejas.create') ?? '', 'fa-church', ['paroquia', 'comunidade']);
        $adicionarAcaoAjuda('Admin master', 'Ver chamados abertos', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=atendimento', 'fa-headset', ['suporte', 'atendimento']);
        $adicionarAcaoAjuda('Admin master', 'Ver chamados encerrados', ($urlAjuda('admin.chamados.index') ?? '') . '?visao=encerrados', 'fa-box-archive', ['resolvidos', 'fechados']);
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Admin local', 'Cadastrar musico', $urlAjuda('local-admin.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe', 'perfil']);
        $adicionarAcaoAjuda('Admin local', 'Gerenciar equipe musical', $urlAjuda('local-admin.musicos.index') ?? '', 'fa-users', ['musicos', 'coordenadores']);
        $adicionarAcaoAjuda('Admin local', 'Montar uma missa', $urlAjuda('local-admin.missas.create') ?? '', 'fa-calendar-plus', ['celebracao', 'repertorio']);
        $adicionarAcaoAjuda('Admin local', 'Ver missas cadastradas', $urlAjuda('local-admin.missas.index') ?? '', 'fa-calendar-check', ['repertorio', 'publicar']);
        $adicionarAcaoAjuda('Admin local', 'Atualizar dados e links da igreja', $urlAjuda('local-admin.church') ?? '', 'fa-link', ['qr', 'publico']);
    }

    if ($usuarioAjuda && $usuarioAjuda->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdAjuda)) {
        $adicionarAcaoAjuda('Coordenador', 'Cadastrar musico', $urlAjuda('coordenador.musicos.create') ?? '', 'fa-user-plus', ['usuario', 'equipe']);
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
                <a href="{{ $acaoAjuda['url'] }}" class="help-action-item flex items-center gap-3 rounded-2xl border border-[#eadfce] bg-white px-4 py-3 text-[#1d1513] transition hover:bg-[#fff7e8]" data-help-item data-help-search-text="{{ $acaoAjuda['busca'] }}">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const painel = document.querySelector('[data-help-panel]');
            const abrir = document.querySelector('[data-help-open]');
            const fechar = document.querySelector('[data-help-close]');
            const busca = document.querySelector('[data-help-search]');
            const itens = Array.from(document.querySelectorAll('[data-help-item]'));
            const vazio = document.querySelector('[data-help-empty]');

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

            abrir?.addEventListener('click', () => mostrarPainel(painel?.classList.contains('hidden')));
            fechar?.addEventListener('click', () => mostrarPainel(false));
            busca?.addEventListener('input', filtrar);

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    mostrarPainel(false);
                }
            });
        });
    </script>
@endif
