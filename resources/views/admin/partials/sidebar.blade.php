<aside
    id="admin_sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-[78vw] max-w-[22rem] -translate-x-full flex-col overflow-hidden bg-green-900 text-white shadow-2xl transition-transform duration-300 md:h-screen md:w-64 md:max-w-none md:translate-x-0"
>
    @php
        $itemMenuClasse = static function (bool $ativo): string {
            return $ativo
                ? 'flex items-center gap-4 rounded-2xl border border-green-700 bg-green-800 px-4 py-3 text-white shadow-sm transition font-semibold group'
                : 'flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group';
        };
    @endphp

    <div class="flex items-center justify-between border-b border-green-800 px-4 py-3 md:hidden">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-9 w-auto shrink-0">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-green-300">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">Menu administrativo</p>
            </div>
        </div>
    </div>

    @auth
        <div class="border-b border-green-800 bg-green-950/50 px-4 py-3 md:hidden">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-green-600 bg-green-800 text-white font-bold shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                    <p class="truncate text-[10px] text-green-300">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    @endauth

    <div class="hidden py-8 md:flex flex-col items-center justify-center border-b border-green-800 bg-green-900 shadow-md relative shrink-0">
        <div class="absolute bg-white opacity-5 w-24 h-24 rounded-full blur-xl top-8"></div>
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="w-28 h-auto mb-4 drop-shadow-2xl hover:scale-105 transition transform duration-300 relative z-10">
        <div class="text-center relative z-10">
            <h1 class="font-extrabold text-xl tracking-widest leading-none text-white drop-shadow-md">VOZ</h1>
            <h2 class="font-bold text-lg tracking-wider text-green-100 opacity-90 leading-tight">&amp; CIFRA</h2>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-3 pb-24 space-y-1.5 md:px-4 md:py-6 md:pb-6 md:space-y-3">
        <a href="{{ route('admin.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.dashboard')) }}">
            <i class="fa-solid fa-house w-5 text-center group-hover:scale-110 transition"></i>
            <span>Painel</span>
        </a>

        <div class="pt-3 pb-1 pl-4 text-[11px] font-black text-green-400 uppercase tracking-widest opacity-70">Administracao central</div>

        <a href="{{ route('admin.igrejas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.igrejas.*')) }}">
            <i class="fa-solid fa-church w-5 text-center group-hover:scale-110 transition"></i>
            <span>Igrejas</span>
        </a>

        <a href="{{ route('admin.musicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.musicos.*')) }}">
            <i class="fa-solid fa-users w-5 text-center group-hover:scale-110 transition"></i>
            <span>Músicos</span>
        </a>

        <a href="{{ route('admin.padres.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.padres.*')) }}">
            <i class="fa-solid fa-user-tie w-5 text-center group-hover:scale-110 transition"></i>
            <span>Padres</span>
        </a>

        <a href="{{ route('admin.acordes.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.acordes.*')) }}">
            <i class="fa-solid fa-guitar w-5 text-center group-hover:scale-110 transition"></i>
            <span>Acordes</span>
        </a>

        <a href="{{ route('admin.tempos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.tempos-liturgicos.*')) }}">
            <i class="fa-solid fa-calendar-days w-5 text-center group-hover:scale-110 transition"></i>
            <span>Tempos liturgicos</span>
        </a>

        <a href="{{ route('admin.momentos-liturgicos.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.momentos-liturgicos.*')) }}">
            <i class="fa-solid fa-list-ol w-5 text-center group-hover:scale-110 transition"></i>
            <span>Momentos liturgicos</span>
        </a>

        <a href="{{ route('admin.musicas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.musicas.*', 'admin.versoes-musicais.*')) }}">
            <i class="fa-solid fa-music w-5 text-center group-hover:scale-110 transition"></i>
            <span>Musicas</span>
        </a>

        <a href="{{ route('admin.settings') }}" class="{{ $itemMenuClasse(request()->routeIs('admin.settings', 'admin.profile', 'admin.profile.update', 'admin.admins-master.store')) }}">
            <i class="fa-solid fa-gear w-5 text-center group-hover:scale-110 transition"></i>
            <span>Configuracoes</span>
        </a>
    </nav>

    <div class="hidden shrink-0 border-t border-green-800 bg-green-950 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] md:block md:pb-4">
        @auth
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                <div class="w-10 h-10 rounded-full bg-green-800 flex items-center justify-center text-white font-bold border border-green-600 shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->nome }}</p>
                    <p class="text-[10px] text-green-400 truncate">{{ auth()->user()->email }}</p>
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
