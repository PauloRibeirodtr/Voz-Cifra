@php
    use App\Enums\PapelIgreja;

    $usuarioSidebar = auth()->user();
    $igrejaAtivaSidebar = $usuarioSidebar?->igrejaAtiva();
    $igrejaAtivaIdSidebar = $igrejaAtivaSidebar?->id;

    $temAdminMasterSidebar = (bool) ($usuarioSidebar?->ehAdminMaster());
    $temAdminLocalSidebar = (bool) ($usuarioSidebar?->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaIdSidebar));
    $temCoordenadorSidebar = (bool) ($usuarioSidebar?->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaIdSidebar));
    $temMusicoSidebar = (bool) ($usuarioSidebar?->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaIdSidebar));
    $temAcessoMusicalSidebar = $temMusicoSidebar || $temCoordenadorSidebar || $temAdminLocalSidebar;

    $isLocalAreaSidebar = request()->routeIs('local-admin.*');
    $isCoordenadorAreaSidebar = request()->routeIs('coordenador.*');

    $sidebarId = $sidebarId ?? 'operational_sidebar';

    $perfilRouteSidebar = $isLocalAreaSidebar
        ? 'local-admin.profile'
        : ($isCoordenadorAreaSidebar ? 'coordenador.profile' : 'member.profile');

    $papeisAtivosSidebar = $usuarioSidebar?->listarPapeisNaIgreja($igrejaAtivaIdSidebar) ?? collect();

    $linkPainelSidebar = $temAdminMasterSidebar
        ? route('admin.dashboard')
        : ($isLocalAreaSidebar ? route('local-admin.dashboard') : ($isCoordenadorAreaSidebar ? route('coordenador.dashboard') : route('member.dashboard')));

    $itemMenuClasseSidebar = static function (bool $ativo): string {
        return $ativo
            ? 'group flex items-center gap-4 rounded-2xl border border-[#8c6933] bg-[#382321] px-4 py-3 font-semibold text-[#fff8ed] shadow-sm transition'
            : 'group flex items-center gap-4 rounded-2xl border border-transparent px-4 py-3 font-medium text-[#f0e4d4] transition hover:border-white/5 hover:bg-[#2b1a19] hover:text-white';
    };

    $secaoLabelClasseSidebar = 'pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest text-[#d6ad6c] opacity-80';
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

    <a href="{{ $linkPainelSidebar }}" class="hidden shrink-0 border-b border-white/10 px-6 py-8 md:flex md:flex-col md:items-center md:justify-center">
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mb-4 h-auto w-24 drop-shadow-2xl">
        <div class="text-center">
            <h1 class="text-xl font-extrabold tracking-widest text-white">VOZ</h1>
            <h2 class="text-lg font-bold tracking-wider text-[#ead6b3]">&amp; CIFRA</h2>
            <p class="mt-2 text-[11px] font-black uppercase tracking-[0.22em] text-[#d6ad6c]">
                {!! $isLocalAreaSidebar ? '&Aacute;rea da igreja' : ($isCoordenadorAreaSidebar ? '&Aacute;rea da coordena&ccedil;&atilde;o' : '&Aacute;rea musical') !!}
            </p>
        </div>
    </a>

    <nav class="flex-1 space-y-3 overflow-y-auto px-4 py-6">
        @if ($temAdminMasterSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Gest&atilde;o central</div>

            <a href="{{ route('admin.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.dashboard')) }}">
                <i class="fa-solid fa-house w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Painel central</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('admin.settings', 'admin.profile', 'admin.profile.update')) }}">
                <i class="fa-solid fa-gear w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>Configura&ccedil;&otilde;es</span>
            </a>
        @endif

        @if ($temAdminLocalSidebar || $temCoordenadorSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Igrejas</div>

            @if ($temAdminLocalSidebar)
                <a href="{{ route('local-admin.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.dashboard')) }}">
                    <i class="fa-solid fa-church w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Painel da igreja</span>
                </a>

                <a href="{{ route('local-admin.church') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.church')) }}">
                    <i class="fa-solid fa-building-circle-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Dados operacionais</span>
                </a>
            @endif

            @if ($temCoordenadorSidebar)
                <a href="{{ route('coordenador.dashboard') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.dashboard')) }}">
                    <i class="fa-solid fa-diagram-project w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Coordena&ccedil;&atilde;o</span>
                </a>
            @endif

            <a href="{{ $temAdminLocalSidebar ? route('local-admin.musicos.index') : route('coordenador.musicos.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.musicos.*', 'coordenador.musicos.*')) }}">
                <i class="fa-solid fa-users w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                <span>{!! $temAdminLocalSidebar ? 'M&uacute;sicos da igreja' : 'Pessoas e v&iacute;nculos' !!}</span>
            </a>
        @endif

        @if ($temAdminLocalSidebar || $temAcessoMusicalSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">Missas e repert&oacute;rios</div>

            @if ($temAdminLocalSidebar)
                <a href="{{ route('local-admin.missas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.missas.*', 'local-admin.repertorio.*')) }}">
                    <i class="fa-solid fa-calendar-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Missas e repert&oacute;rios</span>
                </a>
            @endif

            @if ($temAcessoMusicalSidebar)
                <a href="{{ route('member.repertorio') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.repertorio')) }}">
                    <i class="fa-solid fa-list-check w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Consulta do repert&oacute;rio</span>
                </a>
            @endif
        @endif

        @if ($temCoordenadorSidebar || $temAcessoMusicalSidebar)
            <div class="{{ $secaoLabelClasseSidebar }}">M&uacute;sicas e cifras</div>

            @if ($temCoordenadorSidebar)
                <a href="{{ route('coordenador.musicas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('coordenador.musicas.*', 'coordenador.versoes-musicais.*')) }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Gest&atilde;o musical</span>
                </a>
            @endif

            @if ($temAcessoMusicalSidebar)
                <a href="{{ route('member.musicas.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.musicas.*', 'member.versoes.*')) }}">
                    <i class="fa-solid fa-music w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>Biblioteca musical</span>
                </a>

                <a href="{{ route('member.colecoes.index') }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('member.colecoes.*')) }}">
                    <i class="fa-solid fa-book-open-reader w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
                    <span>&Aacute;rea de estudo</span>
                </a>
            @endif
        @endif

        <div class="{{ $secaoLabelClasseSidebar }}">Conta</div>

        <a href="{{ route($perfilRouteSidebar) }}" class="{{ $itemMenuClasseSidebar(request()->routeIs('local-admin.profile', 'local-admin.profile.update', 'coordenador.profile', 'coordenador.profile.update', 'member.profile', 'member.profile.update', 'member.settings')) }}">
            <i class="fa-solid fa-user-gear w-5 text-center text-[#d6ad6c] transition group-hover:scale-110"></i>
            <span>Perfil e acesso</span>
        </a>
    </nav>

    <div class="shrink-0 border-t border-white/10 bg-black/20 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
        @auth
            <div class="mb-4 rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full border border-[#8c6933] bg-[#6c4a21] font-bold text-white shadow-sm">
                        @if (filled(auth()->user()->foto_perfil_path))
                            <img src="{{ auth()->user()->fotoPerfilUrl() }}" alt="Foto de {{ auth()->user()->nome }}" class="h-full w-full rounded-full object-cover" onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';">
                        @else
                            {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                        <p class="truncate text-[10px] text-[#d6ad6c]">{{ auth()->user()->email }}</p>
                        @if ($igrejaAtivaSidebar)
                            <p class="mt-1 truncate text-[10px] text-white/60">{{ $igrejaAtivaSidebar->nome }}</p>
                        @endif
                    </div>
                </div>

                @if ($papeisAtivosSidebar->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($papeisAtivosSidebar as $papelSidebar)
                            <span class="inline-flex rounded-full border border-[#8c6933] bg-[#382321] px-2.5 py-1 text-[10px] font-semibold text-[#fff1ea]">
                                {{ $papelSidebar->label() }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endauth

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-[#fff1ea] transition hover:bg-white/10">
                Sair da conta
            </button>
        </form>
    </div>
</aside>
