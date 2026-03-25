<aside
    id="admin_sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-screen w-72 max-w-[86vw] -translate-x-full flex-col overflow-hidden bg-green-900 text-white shadow-2xl transition-transform duration-300 md:w-64 md:max-w-none md:translate-x-0"
>
    <div class="flex items-center justify-between border-b border-green-800 px-4 py-4 md:hidden">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="h-11 w-auto">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-green-300">Voz &amp; Cifra</p>
                <p class="text-sm font-semibold text-white/90">Menu administrativo</p>
            </div>
        </div>
    </div>

    <div class="py-8 flex flex-col items-center justify-center border-b border-green-800 bg-green-900 shadow-md relative shrink-0">
        <div class="absolute bg-white opacity-5 w-24 h-24 rounded-full blur-xl top-8"></div>
        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="w-28 h-auto mb-4 drop-shadow-2xl hover:scale-105 transition transform duration-300 relative z-10">
        <div class="text-center relative z-10">
            <h1 class="font-extrabold text-xl tracking-widest leading-none text-white drop-shadow-md">VOZ</h1>
            <h2 class="font-bold text-lg tracking-wider text-green-100 opacity-90 leading-tight">&amp; CIFRA</h2>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto md:px-4 md:py-6 md:space-y-3">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-house w-5 text-center group-hover:scale-110 transition"></i>
            <span>Painel</span>
        </a>

        <div class="pt-4 pb-1 pl-4 text-[11px] font-black text-green-400 uppercase tracking-widest opacity-70">Administracao central</div>

        <a href="{{ route('admin.igrejas.index') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-church w-5 text-center group-hover:scale-110 transition"></i>
            <span>Igrejas</span>
        </a>

        <a href="{{ route('admin.igrejas.create') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-plus w-5 text-center group-hover:scale-110 transition"></i>
            <span>Nova igreja</span>
        </a>

        <a href="{{ route('admin.acordes.index') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-guitar w-5 text-center group-hover:scale-110 transition"></i>
            <span>Acordes</span>
        </a>

        <a href="{{ route('admin.acordes.create') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-music w-5 text-center group-hover:scale-110 transition"></i>
            <span>Novo acorde</span>
        </a>

        <a href="{{ route('admin.tempos-liturgicos.index') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-calendar-days w-5 text-center group-hover:scale-110 transition"></i>
            <span>Tempos liturgicos</span>
        </a>

        <a href="{{ route('admin.tempos-liturgicos.create') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-plus w-5 text-center group-hover:scale-110 transition"></i>
            <span>Novo tempo</span>
        </a>

        <a href="{{ route('admin.momentos-liturgicos.index') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-list-ol w-5 text-center group-hover:scale-110 transition"></i>
            <span>Momentos liturgicos</span>
        </a>

        <a href="{{ route('admin.momentos-liturgicos.create') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-plus w-5 text-center group-hover:scale-110 transition"></i>
            <span>Novo momento</span>
        </a>

        <a href="{{ route('admin.musicas.index') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-music w-5 text-center group-hover:scale-110 transition"></i>
            <span>Musicas</span>
        </a>

        <a href="{{ route('admin.musicas.create') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-plus w-5 text-center group-hover:scale-110 transition"></i>
            <span>Nova musica</span>
        </a>

        <a href="{{ route('admin.profile') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-user w-5 text-center group-hover:scale-110 transition"></i>
            <span>Meu perfil</span>
        </a>

        <a href="{{ route('admin.settings') }}" class="flex items-center gap-4 rounded-2xl px-4 py-3 text-white transition hover:bg-green-800 font-medium group">
            <i class="fa-solid fa-gear w-5 text-center group-hover:scale-110 transition"></i>
            <span>Configuracoes</span>
        </a>
    </nav>

    <div class="p-4 bg-green-950 border-t border-green-800 shrink-0">
        @auth
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-800 flex items-center justify-center text-white font-bold border border-green-600 shadow-sm">
                    {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->nome }}</p>
                    <p class="text-[10px] text-green-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-full hover:bg-red-500/20 text-green-400 hover:text-red-400 transition flex items-center justify-center" title="Sair">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</aside>
