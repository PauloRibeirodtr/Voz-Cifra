@extends('admin.layouts.admin')

@section('title', 'Perfil | Voz & Cifra')
@section('mobile_title', 'Perfil')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
        $igrejaAtivaId = $user->igrejaAtivaId();
        $igrejaSelecionadaNoPerfil = old('igreja_id', $igrejaAtivaId);
        $papeisSelecionadosNoPerfil = old('papeis', $papeisPorIgreja[(string) $igrejaSelecionadaNoPerfil] ?? []);
        $papeisSelecionadosNoPerfil = is_array($papeisSelecionadosNoPerfil) ? array_values($papeisSelecionadosNoPerfil) : [];
    @endphp

    @if (session('status'))
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-4 mb-6 text-sm rounded">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 text-sm rounded">
            {{ session('info') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Conta principal</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Meu perfil</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Aqui voce ajusta a conta global de admin master e tambem pode acumular papeis operacionais por igreja sem criar usuario duplicado.
                </p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(22rem,0.95fr)]">
            <div class="space-y-6">
                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Conta base</p>
                            <h2 class="text-lg font-bold text-gray-800">Dados do admin master</h2>
                            <p class="mt-2 text-sm text-gray-500">A conta global continua separada dos papeis por igreja. Aqui voce atualiza apenas seus dados principais.</p>
                        </div>
                    </div>

                    <div class="admin-panel-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4">
                            @csrf
                            @method('PUT')

                            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <img
                                        id="admin-profile-preview"
                                        src="{{ $user->fotoPerfilUrl() }}"
                                        alt="Foto de perfil de {{ $user->nome }}"
                                        class="h-24 w-24 rounded-3xl border border-gray-200 object-cover bg-white shadow-sm"
                                        data-fallback-src="{{ asset('logo/final.png') }}"
                                        onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';"
                                    />
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700">Foto de perfil</label>
                                        <input
                                            type="file"
                                            name="foto_perfil"
                                            accept="image/*"
                                            class="{{ $classeInput }}"
                                            data-image-preview-input
                                            data-image-preview-target="#admin-profile-preview"
                                            data-default-src="{{ $user->fotoPerfilUrl() }}"
                                        />
                                        <p class="mt-2 text-xs text-gray-500">Use JPG, PNG ou WebP com ate 2 MB. A foto aparece no topo e na navegação lateral.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" value="{{ $user->nome }}" class="{{ $classeInput }} bg-gray-100 text-gray-500" disabled />
                                <p class="text-xs text-gray-500 mt-1">O nome do admin master permanece fixo nesta etapa.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="admin@ministeriomusical.com" class="{{ $classeInput }}" required />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" name="telefone" value="{{ old('telefone', $user->telefone) }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                            </div>

                            <div id="password" data-password-strength-container>
                                <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                                <input type="password" name="password" data-password-strength-input placeholder="Minimo de 8 caracteres" class="{{ $classeInput }}" />
                                @include('partials.password-strength-meter', ['required' => (bool) ($user->primeiro_acesso ?? false)])
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                                <input type="password" name="password_confirmation" data-password-confirmation-input placeholder="Repita a nova senha" class="{{ $classeInput }}" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tema da interface</label>
                                <select name="theme_preference" class="{{ $classeInput }}">
                                    <option value="system" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'system')>Seguir o dispositivo</option>
                                    <option value="light" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'light')>Modo claro</option>
                                    <option value="dark" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'dark')>Modo escuro</option>
                                </select>
                            </div>

                            <button class="admin-btn admin-btn-primary w-full sm:w-auto">Atualizar perfil</button>
                        </form>
                    </div>
                </section>

                <section class="admin-highlight-surface p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Papéis por igreja</p>
                        <h2 class="text-lg font-bold text-gray-800">Vincular meu proprio usuario</h2>
                        <p class="mt-2 text-sm text-gray-500">
                            Use este fluxo para se vincular operacionalmente como admin local, coordenador ou musico em uma ou mais igrejas. Isso nao altera sua conta global de admin master.
                        </p>
                    </div>

                    <form action="{{ route('admin.profile.vinculos.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="admin-label">Igreja</label>
                            <select name="igreja_id" class="admin-select" data-profile-igreja-select required>
                                <option value="">Selecione a igreja</option>
                                @foreach ($igrejas as $igreja)
                                    <option value="{{ $igreja->id }}" @selected((string) $igrejaSelecionadaNoPerfil === (string) $igreja->id)>{{ $igreja->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <span class="admin-label mb-3 block">Papéis operacionais</span>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach (\App\Enums\PapelIgreja::cases() as $papel)
                                    <label class="admin-checkbox rounded-2xl border border-gray-200 bg-white/70 px-4 py-3">
                                        <input
                                            type="checkbox"
                                            name="papeis[]"
                                            value="{{ $papel->value }}"
                                            data-profile-papel-checkbox
                                            @checked(in_array($papel->value, $papeisSelecionadosNoPerfil, true))
                                        >
                                        <span>{{ $papel->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-3 text-sm text-gray-500" data-profile-papeis-hint>
                                Ao selecionar uma igreja, os papeis ja assumidos nela serao marcados automaticamente para facilitar novas acumulacoes no mesmo perfil.
                            </p>
                        </div>

                        <div class="admin-inline-note-warm px-4 py-4 text-sm">
                            Este formulario adiciona apenas papeis por igreja ao seu proprio usuario. Ele nao cria outro admin master e nao muda seu acesso global.
                        </div>

                        <div class="admin-actions">
                            <button type="submit" class="admin-btn admin-btn-secondary">Aplicar vinculo operacional</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Resumo atual</p>
                            <h2 class="text-lg font-bold text-gray-800">Conta e contexto</h2>
                        </div>
                    </div>

                    <div class="admin-panel-body space-y-4 text-sm text-gray-600">
                        <p><strong>Status:</strong> {{ $user->ativo ? 'Ativo' : 'Inativo' }}</p>
                        <p><strong>Acesso global:</strong> Admin master</p>
                        <p><strong>Primeiro acesso:</strong> {{ $user->primeiro_acesso ? 'Pendente' : 'Liberado' }}</p>

                        @if ($igrejasDisponiveisParaAtivacao->isNotEmpty())
                            <form action="{{ route('contexto.igreja-ativa.update') }}" method="POST" class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                @csrf
                                <label class="block text-xs font-black uppercase tracking-[0.16em] text-gray-400">Igreja ativa neste acesso</label>
                                <select name="igreja_id" class="admin-select">
                                    @foreach ($igrejasDisponiveisParaAtivacao as $igrejaDisponivel)
                                        <option value="{{ $igrejaDisponivel->id }}" @selected($igrejaAtivaId === $igrejaDisponivel->id)>
                                            {{ $igrejaDisponivel->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="admin-btn admin-btn-secondary w-full">Trocar igreja ativa</button>
                            </form>
                        @endif
                    </div>
                </section>

                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Vinculos atuais</p>
                            <h2 class="text-lg font-bold text-gray-800">Igrejas e papéis ativos</h2>
                        </div>
                    </div>

                    <div class="admin-panel-body space-y-4">
                        @forelse ($user->vinculosIgreja->where('ativo', true) as $vinculo)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $vinculo->igreja?->nome }}</div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            {{ $vinculo->responsavel_principal ? 'Vinculo principal' : 'Vinculo ativo' }}
                                            @if ($igrejaAtivaId === $vinculo->igreja_id)
                                                • Igreja ativa
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($vinculo->listarPapeisAtivos() as $papel)
                                        <span class="admin-badge admin-badge-success">{{ $papel->label() }}</span>
                                    @empty
                                        <span class="admin-badge admin-badge-neutral">Sem papel ativo</span>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                                Seu usuario ainda nao possui vinculos operacionais por igreja.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.image-preview-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoTelefone = document.querySelector('[data-telefone-input]');
            const imagemPerfil = document.getElementById('admin-profile-preview');
            const selectIgrejaPerfil = document.querySelector('[data-profile-igreja-select]');
            const checkboxesPapeis = Array.from(document.querySelectorAll('[data-profile-papel-checkbox]'));
            const hintPapeis = document.querySelector('[data-profile-papeis-hint]');
            const papeisPorIgreja = @json($papeisPorIgreja);

            if (imagemPerfil) {
                imagemPerfil.addEventListener('error', () => {
                    const fallback = imagemPerfil.dataset.fallbackSrc;

                    if (fallback && imagemPerfil.src !== fallback) {
                        imagemPerfil.src = fallback;
                    }
                }, { once: true });
            }

            if (campoTelefone) {
                campoTelefone.addEventListener('input', () => {
                    let valor = campoTelefone.value.replace(/\D/g, '').slice(0, 11);

                    if (valor.length <= 10) {
                        valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                    } else {
                        valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                    }

                    campoTelefone.value = valor;
                });
            }

            if (selectIgrejaPerfil && checkboxesPapeis.length > 0) {
                const traduzirPapel = (papel) => {
                    return {
                        admin_local: 'Admin local',
                        coordenador: 'Coordenador',
                        musico: 'Musico',
                    }[papel] || papel;
                };

                const sincronizarPapeisDaIgreja = () => {
                    const igrejaId = String(selectIgrejaPerfil.value || '');
                    const papeisAtuais = Array.isArray(papeisPorIgreja[igrejaId]) ? papeisPorIgreja[igrejaId] : [];

                    checkboxesPapeis.forEach((checkbox) => {
                        checkbox.checked = papeisAtuais.includes(checkbox.value);
                    });

                    if (!hintPapeis) {
                        return;
                    }

                    if (!igrejaId) {
                        hintPapeis.textContent = 'Ao selecionar uma igreja, os papeis ja assumidos nela serao marcados automaticamente para facilitar novas acumulacoes no mesmo perfil.';
                        return;
                    }

                    if (papeisAtuais.length === 0) {
                        hintPapeis.textContent = 'Voce ainda nao possui papeis operacionais ativos nesta igreja. Marque o que deseja assumir e aplique o vinculo.';
                        return;
                    }

                    hintPapeis.textContent = `Papeis ja ativos nesta igreja: ${papeisAtuais.map(traduzirPapel).join(', ')}. Voce pode marcar outros para acumular no mesmo vinculo.`;
                };

                selectIgrejaPerfil.addEventListener('change', sincronizarPapeisDaIgreja);
                sincronizarPapeisDaIgreja();
            }
        });
    </script>
    @include('partials.password-strength-script')
@endpush
