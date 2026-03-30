<aside
    id="member_sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-[78vw] max-w-[22rem] -translate-x-full flex-col overflow-hidden bg-slate-900 text-white shadow-2xl transition-transform duration-300 md:h-screen md:w-64 md:max-w-none md:translate-x-0"
>
    @php
        $itemMenuClasse = static function (bool $ativo): string {
            return $ativo
                ? 'flex items-center gap-4 rounded-2xl border border-emerald-700 bg-emerald-800 px-4 py-3 font-semibold text-white shadow-sm transition group'
                : 'flex items-center gap-4 rounded-2xl px-4 py-3 font-medium text-slate-100 transition hover:bg-slate-800 group';
        };
    @endphp

    <div class="flex items-center justify-between border-b border-slate-800 px-4 py-3 md:hidden">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-9 w-auto shrink-0">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-300">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">Menu do musico</p>
            </div>
        </div>
    </div>

    @auth
        <div class="border-b border-slate-800 bg-slate-950/50 px-4 py-3 md:hidden">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-emerald-600 bg-emerald-800 font-bold text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                    <p class="truncate text-[10px] text-emerald-300">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    @endauth

    <div class="hidden shrink-0 border-b border-slate-800 bg-slate-900 px-6 py-8 md:flex md:flex-col md:items-center md:justify-center">
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mb-4 h-auto w-24 drop-shadow-2xl">
        <div class="text-center">
            <h1 class="text-xl font-extrabold tracking-widest text-white">VOZ</h1>
            <h2 class="text-lg font-bold tracking-wider text-slate-200">&amp; CIFRA</h2>
            <p class="mt-2 text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Area do musico</p>
        </div>
    </div>

    <nav class="flex-1 space-y-3 overflow-y-auto px-4 py-6">
        <a href="{{ route('member.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('member.dashboard')) }}">
            <i class="fa-solid fa-house w-5 text-center transition group-hover:scale-110"></i>
            <span>Painel</span>
        </a>

        <a href="{{ route('member.repertorio') }}" class="{{ $itemMenuClasse(request()->routeIs('member.repertorio')) }}">
            <i class="fa-solid fa-list-check w-5 text-center transition group-hover:scale-110"></i>
            <span>Meu repertorio</span>
        </a>

        <a href="{{ route('member.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('member.musicas.*', 'member.versoes.*')) }}">
            <i class="fa-solid fa-music w-5 text-center transition group-hover:scale-110"></i>
            <span>Biblioteca musical</span>
        </a>

        <div class="pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Conta</div>

        <a href="{{ route('member.profile') }}" class="{{ $itemMenuClasse(request()->routeIs('member.profile', 'member.profile.update')) }}">
            <i class="fa-solid fa-user-pen w-5 text-center transition group-hover:scale-110"></i>
            <span>Meu perfil</span>
        </a>

        <a href="{{ route('member.settings') }}" class="{{ $itemMenuClasse(request()->routeIs('member.settings')) }}">
            <i class="fa-solid fa-gear w-5 text-center transition group-hover:scale-110"></i>
            <span>Configuracoes</span>
        </a>
    </nav>

    <div class="shrink-0 border-t border-slate-800 bg-slate-950 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
        @auth
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-600 bg-emerald-800 font-bold text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                    <p class="truncate text-[10px] text-emerald-300">{{ auth()->user()->email }}</p>
                </div>
            </div>
        @endauth

        <div class="grid grid-cols-1 gap-2">
            <a href="{{ route('member.settings') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-100 transition hover:border-emerald-600 hover:bg-emerald-950 hover:text-white">
                Configuracoes
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-red-900 bg-red-950 px-4 py-3 text-sm font-semibold text-red-100 transition hover:bg-red-900">
                    Sair da conta
                </button>
            </form>
        </div>
    </div>
</aside>

