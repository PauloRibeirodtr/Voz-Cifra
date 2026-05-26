@php
    use App\Enums\PapelIgreja;

    $usuarioSidebar = auth()->user();
    $igrejaAtivaSidebar = $usuarioSidebar ? $usuarioSidebar->igrejaAtiva() : null;
    $igrejaAtivaIdSidebar = $igrejaAtivaSidebar ? $igrejaAtivaSidebar->id : null;

    $temAdminMasterSidebar = (bool) ($usuarioSidebar && $usuarioSidebar->ehAdminMaster());
    $temAdminLocalSidebar = (bool) ($usuarioSidebar && $usuarioSidebar->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdSidebar));
    $temCoordenadorSidebar = (bool) ($usuarioSidebar && $usuarioSidebar->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdSidebar));
    $temMusicoSidebar = (bool) ($usuarioSidebar && $usuarioSidebar->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdSidebar));
    $temAcessoMusicalSidebar = $temMusicoSidebar || $temCoordenadorSidebar || $temAdminLocalSidebar;
    $temPapelOperacionalSidebar = $temAdminLocalSidebar || $temCoordenadorSidebar || $temMusicoSidebar;

    $isLocalAreaSidebar = request()->routeIs('local-admin.*');
    $isCoordenadorAreaSidebar = request()->routeIs('coordenador.*');

    $sidebarId = $sidebarId ?? 'operational_sidebar';

    $perfilRouteSidebar = $isLocalAreaSidebar
        ? 'local-admin.profile'
        : ($isCoordenadorAreaSidebar ? 'coordenador.profile' : 'member.profile');

    $papeisAtivosSidebar = $usuarioSidebar ? $usuarioSidebar->listarPapeisNaIgreja($igrejaAtivaIdSidebar) : collect();

    $linkPainelSidebar = $temAdminMasterSidebar
        ? route('admin.dashboard')
        : ($isLocalAreaSidebar ? route('local-admin.dashboard') : ($isCoordenadorAreaSidebar ? route('coordenador.dashboard') : route('member.dashboard')));

    $itemMenuClasseSidebar = static function (bool $ativo): string {
        return $ativo
            ? 'group flex items-center gap-3 rounded-xl border border-[#8c6933]/70 bg-[#382321] px-3.5 py-2.5 font-semibold text-[#fff8ed] shadow-sm transition'
            : 'group flex items-center gap-3 rounded-xl border border-transparent px-3.5 py-2.5 font-medium text-[#f0e4d4] transition hover:border-white/5 hover:bg-[#2b1a19] hover:text-white';
    };

    $secaoLabelClasseSidebar = 'pt-3 pb-1 pl-3.5 text-[10px] font-black uppercase tracking-[0.2em] text-[#d6ad6c] opacity-80';
@endphp

<aside
    id="{{ $sidebarId }}"
    class="fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-[80vw] max-w-[22rem] -translate-x-full flex-col overflow-hidden bg-[#1a1111] text-white shadow-2xl transition-transform duration-300 md:h-screen md:w-72 md:max-w-none md:translate-x-0"
>
    <div class="border-b border-white/10 px-5 py-4 md:hidden">
        <a href="{{ $linkPainelSidebar }}" class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-10 w-auto shrink-0">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#d6ad6c]">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">Acesso operacional</p>
            </div>
        </a>
    </div>

    <a href="{{ $linkPainelSidebar }}" class="hidden shrink-0 border-b border-white/10 px-6 py-5 md:flex md:items-center md:gap-4">
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-16 w-16 shrink-0 rounded-2xl object-contain drop-shadow-xl">
        <div class="min-w-0">
            <h1 class="text-lg font-black tracking-widest text-white">VOZ</h1>
            <h2 class="-mt-1 text-base font-bold tracking-wider text-[#ead6b3]">&amp; CIFRA</h2>
            <p class="mt-1 text-[10px] font-black uppercase tracking-[0.18em] text-[#d6ad6c]">
                {!! $isLocalAreaSidebar ? '&Aacute;rea da igreja' : ($isCoordenadorAreaSidebar ? '&Aacute;rea da coordena&ccedil;&atilde;o' : '&Aacute;rea musical') !!}
            </p>
        </div>
    </a>

    <nav class="flex-1 space-y-2 overflow-y-auto px-3.5 py-4">
        @if ($temAdminMasterSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Painel</div>

            <a href="{{ route('admin.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.dashboard')) }}">
                <i class="fa-solid fa-house w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Painel central</span>
            </a>

            <div class="{{ $secaoLabelClasseSidebar }}">Administra&ccedil;&atilde;o central</div>

            <a href="{{ route('admin.igrejas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.igrejas.*')) }}">
                <i class="fa-solid fa-church w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Igrejas</span>
            </a>

            <a href="{{ route('admin.usuarios.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.usuarios.*')) }}">
                <i class="fa-solid fa-users w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Usu&aacute;rios</span>
            </a>

        @endif

        @if (!$temAdminMasterSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Painel</div>

            <a href="{{ $linkPainelSidebar }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.dashboard', 'coordenador.dashboard', 'member.dashboard')) }}">
                <i class="fa-solid fa-house w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Painel</span>
            </a>
        @endif

        @if ($temPapelOperacionalSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Minha igreja</div>

            @if ($igrejaAtivaSidebar)
                <div class="mx-0.5 rounded-xl border border-[#8c6933]/25 bg-[#251716] px-3.5 py-3 text-sm text-[#f6ead4]">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#d6ad6c]">Igreja selecionada</p>
                    <p class="mt-1 line-clamp-2 font-semibold leading-5 text-white">{{ $igrejaAtivaSidebar->nome }}</p>
                </div>
            @endif

            @if ($temAdminLocalSidebar)
                <a href="{{ route('local-admin.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.dashboard')) }}">
                    <i class="fa-solid fa-church w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Resumo da igreja</span>
                </a>

                <a href="{{ route('local-admin.church') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.church')) }}">
                    <i class="fa-solid fa-building-circle-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Dados e links</span>
                </a>
            @endif

            @if ($temCoordenadorSidebar)
                <a href="{{ route('coordenador.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.dashboard')) }}">
                    <i class="fa-solid fa-diagram-project w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Coordena&ccedil;&atilde;o musical</span>
                </a>
            @endif

            @if ($temAdminLocalSidebar || $temCoordenadorSidebar)
                <a href="{{ $temAdminLocalSidebar ? route('local-admin.musicos.index') : route('coordenador.musicos.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.musicos.*', 'coordenador.musicos.*')) }}">
                    <i class="fa-solid fa-users w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Equipe musical</span>
                </a>
            @endif
        @endif

        @if ($temAdminLocalSidebar || $temAcessoMusicalSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Celebra&ccedil;&otilde;es</div>

            @if ($temAdminLocalSidebar)
                <a href="{{ route('local-admin.missas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.missas.*', 'local-admin.repertorio.*')) }}">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Missas</span>
                </a>
            @endif

            @if ($temAcessoMusicalSidebar)
                <a href="{{ route('member.repertorio') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.repertorio')) }}">
                    <i class="fa-solid fa-list-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Repert&oacute;rio</span>
                </a>
            @endif
        @endif

        @if ($temCoordenadorSidebar || $temAcessoMusicalSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">M&uacute;sicas</div>

            @if ($temCoordenadorSidebar)
                <a href="{{ route('coordenador.musicas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.musicas.*', 'coordenador.versoes-musicais.*')) }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Cadastrar cifras</span>
                </a>

                <a href="{{ route('coordenador.tempos-liturgicos.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.tempos-liturgicos.*')) }}">
                    <i class="fa-solid fa-calendar-days w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Tempos lit&uacute;rgicos</span>
                </a>

                <a href="{{ route('coordenador.momentos-liturgicos.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.momentos-liturgicos.*')) }}">
                    <i class="fa-solid fa-list-ol w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Momentos lit&uacute;rgicos</span>
                </a>
            @endif

            @if ($temAcessoMusicalSidebar)
                <a href="{{ route('member.musicas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.musicas.*', 'member.versoes.*')) }}">
                    <i class="fa-solid fa-music w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Consultar m&uacute;sicas</span>
                </a>

                <a href="{{ route('member.colecoes.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.colecoes.*')) }}">
                    <i class="fa-solid fa-book-open-reader w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Meus estudos</span>
                </a>
            @endif
        @endif

        @if ($temAdminMasterSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Sistema</div>

            <a href="{{ route('admin.auditoria.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.auditoria.*')) }}">
                <i class="fa-solid fa-shield-halved w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Auditoria</span>
            </a>

            <a href="{{ route('admin.chamados.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.chamados.*')) }}">
                <i class="fa-solid fa-headset w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Chamados</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.settings')) }}">
                <i class="fa-solid fa-gear w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Configura&ccedil;&otilde;es</span>
            </a>
        @endif

        <div class="{{ $secaoLabelClasseSidebar }}">Conta</div>

        @if (auth()->user() && auth()->user()->ehMembro())
            <a href="{{ route('member.chamados.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.chamados.*')) }}">
                <i class="fa-solid fa-message w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Suporte</span>
            </a>
        @endif

        <a href="{{ route($perfilRouteSidebar) }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.profile', 'local-admin.profile.update', 'coordenador.profile', 'coordenador.profile.update', 'member.profile', 'member.profile.update', 'member.settings')) }}">
            <i class="fa-solid fa-user-gear w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
            <span>Configura&ccedil;&otilde;es</span>
        </a>
    </nav>

    <div class="shrink-0 border-t border-white/10 bg-black/20 p-3 pb-[max(.75rem,env(safe-area-inset-bottom))]">
        @auth
            <a href="{{ route($perfilRouteSidebar) }}" class="admin-sidebar-profile-link mb-3 block rounded-xl border border-white/10 bg-white/[0.04] px-3 py-3 transition hover:border-[#8c6933]/50 hover:bg-white/[0.07]" aria-label="Abrir meu perfil">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[#8c6933] bg-[#6c4a21] font-bold text-white shadow-sm">
                        @if (filled(auth()->user()->foto_perfil_path))
                            <img
                                src="{{ auth()->user()->fotoPerfilUrl() }}"
                                alt="Foto de {{ auth()->user()->nome }}"
                                class="h-full w-full rounded-full object-cover"
                                data-fallback-logo="{{ asset('logo/final.png') }}"
                                onerror="this.onerror=null;this.src=this.dataset.fallbackLogo;"
                            >
                        @else
                            {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-black leading-5 text-white">{{ auth()->user()->nome }}</p>
                        <p class="truncate text-[11px] font-medium text-[#d6ad6c]">{{ auth()->user()->email }}</p>
                        @if ($igrejaAtivaSidebar)
                            <p class="mt-1 line-clamp-1 text-[10px] font-semibold text-white/55">{{ $igrejaAtivaSidebar->nome }}</p>
                        @endif
                    </div>
                </div>

                @if ($papeisAtivosSidebar->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($papeisAtivosSidebar as $papelSidebar)
                            <span class="inline-flex rounded-full border border-[#8c6933]/70 bg-[#382321] px-2.5 py-1 text-[10px] font-bold text-[#fff1ea]">
                                {{ $papelSidebar->label() }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </a>
        @endauth

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-[#fff1ea] transition hover:bg-white/10">
                Sair da conta
            </button>
        </form>
    </div>
</aside>
