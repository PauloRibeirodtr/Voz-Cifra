<aside
    id="local_sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-[78vw] max-w-[22rem] -translate-x-full flex-col overflow-hidden bg-green-900 text-white shadow-2xl transition-transform duration-300 md:h-screen md:w-64 md:max-w-none md:translate-x-0"
>
    @php
        $itemMenuClasse = static function (bool $ativo): string {
            return $ativo
                ? 'flex items-center gap-4 rounded-2xl border border-green-700 bg-green-800 px-4 py-3 font-semibold text-white shadow-sm transition group'
                : 'flex items-center gap-4 rounded-2xl px-4 py-3 font-medium text-white transition hover:bg-green-800 group';
        };
    @endphp

    <div class="flex items-center justify-between border-b border-green-800 px-4 py-3 md:hidden">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-9 w-auto shrink-0">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-green-300">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">Menu da igreja</p>
            </div>
        </div>
    </div>

    @auth
        <div class="border-b border-green-800 bg-green-950/50 px-4 py-3 md:hidden">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-green-600 bg-green-800 font-bold text-white shadow-sm">
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
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="w-24 h-auto mb-4 drop-shadow-2xl relative z-10">
        <div class="text-center relative z-10">
            <h1 class="font-extrabold text-xl tracking-widest leading-none text-white drop-shadow-md">VOZ</h1>
            <h2 class="font-bold text-lg tracking-wider text-green-100 opacity-90 leading-tight">&amp; CIFRA</h2>
            <p class="mt-2 text-[11px] font-black uppercase tracking-[0.22em] text-green-300">Area da igreja</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-3 pb-24 space-y-1.5 md:px-4 md:py-6 md:pb-6 md:space-y-3">
        <a href="{{ route('local-admin.dashboard') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.dashboard')) }}">
            <i class="fa-solid fa-house w-5 text-center group-hover:scale-110 transition"></i>
            <span>Painel</span>
        </a>

        <div class="pt-3 pb-1 pl-4 text-[11px] font-black uppercase tracking-widest text-green-400 opacity-70">Gestao da igreja</div>

        <a href="{{ route('local-admin.church') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.church')) }}">
            <i class="fa-solid fa-church w-5 text-center group-hover:scale-110 transition"></i>
            <span>Minha igreja</span>
        </a>

        <a href="{{ route('local-admin.missas.index') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.missas.*', 'local-admin.repertorio.*')) }}">
            <i class="fa-solid fa-calendar-check w-5 text-center group-hover:scale-110 transition"></i>
            <span>Missas</span>
        </a>

        <a href="{{ route('local-admin.profile') }}" class="{{ $itemMenuClasse(request()->routeIs('local-admin.profile', 'local-admin.profile.update')) }}">
            <i class="fa-solid fa-user-pen w-5 text-center group-hover:scale-110 transition"></i>
            <span>Meu perfil</span>
        </a>
    </nav>

    <div class="hidden shrink-0 border-t border-green-800 bg-green-950 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] md:block md:pb-4">
        @auth
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-green-600 bg-green-800 font-bold text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->nome }}</p>
                    <p class="truncate text-[10px] text-green-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
        @endauth
    </div>
</aside>
