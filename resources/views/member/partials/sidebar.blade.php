@php
    $isCoordenadorArea = request()->routeIs('coordenador.*');
    $routePrefix = $isCoordenadorArea ? 'coordenador' : 'member';
    $tituloArea = $isCoordenadorArea ? 'Menu do coordenador' : 'Menu do musico';

    $itemMenuClasse = static function (bool $ativo): string {
        return $ativo
            ? 'group flex items-center gap-4 rounded-2xl border border-emerald-500/40 bg-emerald-700 px-4 py-3 font-semibold text-white shadow-sm transition'
            : 'group flex items-center gap-4 rounded-2xl border border-transparent px-4 py-3 font-medium text-slate-100 transition hover:border-white/5 hover:bg-white/5';
    };
@endphp

<aside
    id="member_sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-[80vw] max-w-[22rem] -translate-x-full flex-col overflow-hidden bg-slate-900 text-white shadow-2xl transition-transform duration-300 md:h-screen md:w-72 md:max-w-none md:translate-x-0"
>
    <div class="border-b border-white/10 px-5 py-4 md:hidden">
        <a href="{{ route($routePrefix . '.dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-10 w-auto shrink-0">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-300">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">{{ $tituloArea }}</p>
            </div>
        </a>
    </div>

    <a href="{{ route($routePrefix . '.dashboard') }}" class="hidden shrink-0 border-b border-white/10 px-6 py-8 md:flex md:flex-col md:items-center md:justify-center">
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mb-4 h-auto w-24 drop-shadow-2xl">
        <div class="text-center">
            <h1 class="text-xl font-extrabold tracking-widest text-white">VOZ</h1>
            <h2 class="text-lg font-bold tracking-wider text-slate-200">&amp; CIFRA</h2>
            <p class="mt-2 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">{{ $isCoordenadorArea ? 'Area do coordenador' : 'Area do musico' }}</p>
        </div>
    </a>

    <nav class="flex-1 space-y-3 overflow-y-auto px-4 py-6">
        <a href="{{ route($routePrefix . '.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs($routePrefix . '.dashboard')) }}">
            <i class="fa-solid fa-house w-5 text-center text-white transition group-hover:scale-110"></i>
            <span>Painel</span>
        </a>

        @if ($isCoordenadorArea)
            <a href="{{ route('coordenador.musicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.musicos.*')) }}">
                <i class="fa-solid fa-users w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Musicos</span>
            </a>

            <a href="{{ route('coordenador.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.musicas.*', 'coordenador.versoes-musicais.*')) }}">
                <i class="fa-solid fa-music w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Musicas e versoes</span>
            </a>

            <a href="{{ route('coordenador.tempos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.tempos-liturgicos.*')) }}">
                <i class="fa-solid fa-calendar-days w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Tempos liturgicos</span>
            </a>

            <a href="{{ route('coordenador.momentos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('coordenador.momentos-liturgicos.*')) }}">
                <i class="fa-solid fa-list-ol w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Momentos liturgicos</span>
            </a>
        @else
            <a href="{{ route('member.repertorio') }}" class="{{ $itemMenuClasse(request()->routeIs('member.repertorio')) }}">
                <i class="fa-solid fa-list-check w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Meu repertorio</span>
            </a>

            <a href="{{ route('member.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('member.musicas.*', 'member.versoes.*')) }}">
                <i class="fa-solid fa-music w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Biblioteca musical</span>
            </a>

            <a href="{{ route('member.colecoes.index') }}" class="{{ $itemMenuClasse(request()->routeIs('member.colecoes.*')) }}">
                <i class="fa-solid fa-compact-disc w-5 text-center text-white transition group-hover:scale-110"></i>
                <span>Playlists salvas</span>
            </a>
        @endif

        <div class="pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Conta</div>

        <a href="{{ route($routePrefix . '.profile') }}" class="{{ $itemMenuClasse(request()->routeIs($routePrefix . '.profile', $routePrefix . '.profile.update', 'member.settings')) }}">
            <i class="fa-solid fa-user-gear w-5 text-center text-white transition group-hover:scale-110"></i>
            <span>Perfil e acesso</span>
        </a>
    </nav>

    <div class="shrink-0 border-t border-white/10 bg-slate-950/80 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
        @auth
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full border border-emerald-500/70 bg-emerald-700 font-bold text-white shadow-sm">
                    @if (filled(auth()->user()->foto_perfil_path))
                        <img src="{{ auth()->user()->fotoPerfilUrl() }}" alt="Foto de {{ auth()->user()->nome }}" class="h-full w-full rounded-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                    <p class="truncate text-[10px] text-emerald-300">{{ auth()->user()->email }}</p>
                </div>
            </div>
        @endauth

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-red-900 bg-red-950 px-4 py-3 text-sm font-semibold text-red-100 transition hover:bg-red-900">
                Sair da conta
            </button>
        </form>
    </div>
</aside>
