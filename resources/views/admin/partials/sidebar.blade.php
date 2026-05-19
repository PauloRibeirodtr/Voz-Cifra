<aside
    id="admin_sidebar"
    class="admin-sidebar flex flex-col overflow-hidden text-white"
    aria-hidden="true"
>
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
        $temPapelOperacionalSidebar = $temAdminLocalSidebar || $temCoordenadorSidebar || $temMusicoSidebar;
        $papeisAtivosSidebar = $usuarioSidebar?->listarPapeisNaIgreja($igrejaAtivaIdSidebar) ?? collect();
        $linkPessoasSidebar = $temAdminLocalSidebar
            ? route('local-admin.musicos.index')
            : ($temCoordenadorSidebar ? route('coordenador.musicos.index') : null);
        $perfilRouteSidebar = request()->routeIs('local-admin.*')
            ? 'local-admin.profile'
            : (request()->routeIs('coordenador.*') ? 'coordenador.profile' : (request()->routeIs('member.*') ? 'member.profile' : 'admin.profile'));
        $linkPainelSidebar = $temAdminMasterSidebar
            ? route('admin.dashboard')
            : ($temAdminLocalSidebar ? route('local-admin.dashboard') : ($temCoordenadorSidebar ? route('coordenador.dashboard') : route('member.dashboard')));
        $rotuloMenuSidebar = $temAdminMasterSidebar
            ? 'Menu administrativo'
            : ($temAdminLocalSidebar ? 'Menu da igreja' : ($temCoordenadorSidebar ? 'Menu da coordena&ccedil;&atilde;o' : 'Menu do m&uacute;sico'));

        $itemMenuClasse = static function (bool $ativo): string {
            return $ativo
                ? 'admin-sidebar-link admin-sidebar-link-active font-semibold group'
                : 'admin-sidebar-link font-medium group';
        };
    @endphp

    <div class="admin-sidebar-inner">
        <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3 lg:hidden">
            <a href="{{ $linkPainelSidebar }}" class="flex items-center gap-3">
                <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-9 w-auto shrink-0">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#d6ad6c]">Voz &amp; Cifra</p>
                    <p class="text-sm font-semibold text-white/90">{!! $rotuloMenuSidebar !!}</p>
                </div>
            </a>

            <button
                type="button"
                id="admin_sidebar_close"
                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-[#f3dfbd] transition hover:bg-white/10"
                aria-label="Fechar menu"
            >
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        @auth
            <div class="border-b border-white/10 bg-black/10 px-4 py-3 lg:hidden">
                <a href="{{ route($perfilRouteSidebar) }}" class="admin-sidebar-profile-link flex items-center gap-3 rounded-2xl px-2 py-2" aria-label="Abrir meu perfil">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[#8c6933] bg-[#6c4a21] text-white font-bold shadow-sm">
                        @if (filled(auth()->user()->foto_perfil_path))
                            <img
                                src="{{ auth()->user()->fotoPerfilUrl() }}"
                                alt="Foto de {{ auth()->user()->nome }}"
                                class="admin-avatar-image"
                                onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';"
                            >
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
                </a>

                @if ($papeisAtivosSidebar->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2 px-2 pb-1">
                        @foreach ($papeisAtivosSidebar as $papelSidebar)
                            <span class="inline-flex rounded-full border border-[#8c6933] bg-[#382321] px-2.5 py-1 text-[10px] font-semibold text-[#fff1ea]">
                                {{ $papelSidebar->label() }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endauth

        <a href="{{ $linkPainelSidebar }}" class="admin-sidebar-brand relative hidden shrink-0 flex-col items-center justify-center border-b border-white/10 px-5 py-5 text-center shadow-md lg:flex">
            <div class="absolute bg-white opacity-5 w-20 h-20 rounded-full blur-xl top-5"></div>
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="relative z-10 mb-3 h-auto w-16 drop-shadow-2xl transition duration-300 hover:scale-105">
            <div class="relative z-10 text-center">
                <h1 class="font-extrabold text-lg tracking-widest leading-none text-white drop-shadow-md">VOZ</h1>
                <h2 class="font-bold text-base tracking-wider text-[#ead6b3] opacity-90 leading-tight">&amp; CIFRA</h2>
            </div>
        </a>

        <div class="admin-sidebar-body">
            <nav class="admin-sidebar-nav">
                @if ($temAdminMasterSidebar)
                    <a href="{{ route('admin.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.dashboard')) }}">
                        <i class="fa-solid fa-house w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Painel</span>
                    </a>

                    <div class="admin-sidebar-section-label pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest opacity-80">Administra&ccedil;&atilde;o central</div>

                    <a href="{{ route('admin.igrejas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.igrejas.*')) }}">
                        <i class="fa-solid fa-church w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Igrejas</span>
                    </a>

                    <a href="{{ route('admin.usuarios.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.usuarios.*')) }}">
                        <i class="fa-solid fa-users w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Usu&aacute;rios</span>
                    </a>
                @else
                    <a href="{{ $linkPainelSidebar }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.dashboard', 'coordenador.dashboard', 'member.dashboard')) }}">
                        <i class="fa-solid fa-house w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Painel</span>
                    </a>
                @endif

                @if ($temPapelOperacionalSidebar)
                    <div class="admin-sidebar-section-label pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest opacity-80">Minha igreja</div>

                    @if ($igrejaAtivaSidebar)
                        <div class="mx-3 rounded-2xl border border-[#8c6933]/25 bg-[#251716] px-4 py-3 text-sm text-[#f6ead4]">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#d6ad6c]">Igreja selecionada</p>
                            <p class="mt-1 font-semibold text-white">{{ $igrejaAtivaSidebar->nome }}</p>
                        </div>
                    @endif

                    @if ($temAdminLocalSidebar)
                        <a href="{{ route('local-admin.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.dashboard')) }}">
                            <i class="fa-solid fa-church w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Resumo da igreja</span>
                        </a>

                        <a href="{{ route('local-admin.church') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.church')) }}">
                            <i class="fa-solid fa-building-circle-check w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Dados e links</span>
                        </a>
                    @endif

                    @if ($temCoordenadorSidebar)
                        <a href="{{ route('coordenador.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.dashboard')) }}">
                            <i class="fa-solid fa-diagram-project w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Coordena&ccedil;&atilde;o musical</span>
                        </a>
                    @endif

                    @if ($linkPessoasSidebar)
                        <a href="{{ $linkPessoasSidebar }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.musicos.*', 'coordenador.musicos.*')) }}">
                            <i class="fa-solid fa-users w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Equipe musical</span>
                        </a>
                    @endif
                @endif

                @if ($temAdminLocalSidebar || $temAcessoMusicalSidebar)
                    <div class="admin-sidebar-section-label pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest opacity-80">Celebra&ccedil;&otilde;es</div>

                    @if ($temAdminLocalSidebar)
                        <a href="{{ route('local-admin.missas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.missas.*', 'local-admin.repertorio.*')) }}">
                            <i class="fa-solid fa-calendar-check w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Missas</span>
                        </a>
                    @endif

                    @if ($temAcessoMusicalSidebar)
                        <a href="{{ route('member.repertorio') }}" class="{{ $itemMenuClasse(request()->routeIs('member.repertorio')) }}">
                            <i class="fa-solid fa-list-check w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Repert&oacute;rio</span>
                        </a>
                    @endif
                @endif

                @if ($temCoordenadorSidebar || $temAcessoMusicalSidebar || auth()->user()?->ehAdminMaster())
                    <div class="admin-sidebar-section-label pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest opacity-80">M&uacute;sicas e acordes</div>

                    @if ($temCoordenadorSidebar)
                        <a href="{{ route('coordenador.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.musicas.*', 'coordenador.versoes-musicais.*')) }}">
                            <i class="fa-solid fa-sliders w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Cadastrar m&uacute;sicas e cifras</span>
                        </a>

                        <a href="{{ route('coordenador.tempos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.tempos-liturgicos.*')) }}">
                            <i class="fa-solid fa-calendar-days w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Tempos lit&uacute;rgicos</span>
                        </a>

                        <a href="{{ route('coordenador.momentos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.momentos-liturgicos.*')) }}">
                            <i class="fa-solid fa-list-ol w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Momentos lit&uacute;rgicos</span>
                        </a>
                    @endif

                    @if ($temAcessoMusicalSidebar)
                        <a href="{{ route('member.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('member.musicas.*', 'member.versoes.*')) }}">
                            <i class="fa-solid fa-music w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Consultar biblioteca</span>
                        </a>

                        <a href="{{ route('member.colecoes.index') }}" class="{{ $itemMenuClasse(request()->routeIs('member.colecoes.*')) }}">
                            <i class="fa-solid fa-book-open-reader w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Meus estudos</span>
                        </a>
                    @endif

                    @if (auth()->user()?->ehAdminMaster())
                        <a href="{{ route('admin.tempos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.tempos-liturgicos.*')) }}">
                            <i class="fa-solid fa-calendar-days w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Tempos lit&uacute;rgicos</span>
                        </a>

                        <a href="{{ route('admin.momentos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.momentos-liturgicos.*')) }}">
                            <i class="fa-solid fa-list-ol w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Momentos lit&uacute;rgicos</span>
                        </a>

                        <a href="{{ route('admin.acordes.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.acordes.*')) }}">
                            <i class="fa-solid fa-guitar w-5 text-center group-hover:scale-110 transition"></i>
                            <span>Acordes</span>
                        </a>
                    @endif
                @endif

                @if ($temAdminMasterSidebar)
                    <div class="admin-sidebar-section-label pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest opacity-80">Sistema</div>

                    <a href="{{ route('admin.auditoria.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.auditoria.*')) }}">
                        <i class="fa-solid fa-shield-halved w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Auditoria</span>
                    </a>

                    <a href="{{ route('admin.chamados.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.chamados.*')) }}">
                        <i class="fa-solid fa-headset w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Chamados</span>
                    </a>

                    <a href="{{ route('admin.settings') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.settings', 'admin.profile', 'admin.profile.update')) }}">
                        <i class="fa-solid fa-gear w-5 text-center group-hover:scale-110 transition"></i>
                        <span>Configura&ccedil;&otilde;es</span>
                    </a>
                @endif
            </nav>

            <div class="admin-sidebar-footer">
                @auth
                    <a href="{{ route($perfilRouteSidebar) }}" class="admin-sidebar-profile admin-sidebar-profile-link mb-4 hidden items-center gap-3 rounded-2xl px-3 py-3 lg:flex" aria-label="Abrir meu perfil">
                        <div class="w-10 h-10 rounded-full bg-[#6c4a21] flex items-center justify-center text-white font-bold border border-[#8c6933] shadow-sm">
                            @if (filled(auth()->user()->foto_perfil_path))
                                <img
                                    src="{{ auth()->user()->fotoPerfilUrl() }}"
                                    alt="Foto de {{ auth()->user()->nome }}"
                                    class="admin-avatar-image"
                                    onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';"
                                >
                            @else
                                {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ auth()->user()->nome }}</p>
                            <p class="text-[10px] text-[#d6ad6c] truncate">{{ auth()->user()->email }}</p>
                            @if ($igrejaAtivaSidebar)
                                <p class="mt-1 truncate text-[10px] text-white/60">{{ $igrejaAtivaSidebar->nome }}</p>
                            @endif
                        </div>
                    </a>

                    @if ($papeisAtivosSidebar->isNotEmpty())
                        <div class="mb-4 hidden flex-wrap gap-2 lg:flex">
                            @foreach ($papeisAtivosSidebar as $papelSidebar)
                                <span class="inline-flex rounded-full border border-[#8c6933] bg-[#382321] px-2.5 py-1 text-[10px] font-semibold text-[#fff1ea]">
                                    {{ $papelSidebar->label() }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endauth

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-[#fff1ea] transition hover:bg-white/10">
                        Sair da conta
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
