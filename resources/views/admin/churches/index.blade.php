@extends('admin.layouts.admin')

@section('title', 'Igrejas | Voz & Cifra')
@section('mobile_title', 'Igrejas')

@section('content')
    @php
        $totalIgrejas = $igrejas->count();
        $igrejasOperacionais = $igrejas->filter(fn ($igreja) => $igreja->estaOperacional())->count();
        $igrejasAguardando = $totalIgrejas - $igrejasOperacionais;
    @endphp

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800">Igrejas cadastradas</h1>
            <p class="text-sm text-gray-500">Gerencie as igrejas, acompanhe o status operacional, visualize os links públicos e confira quem já está vinculado localmente.</p>
        </div>

        <a href="{{ route('admin.igrejas.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#6c4a21] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#7a5528] sm:self-start">
            Criar nova igreja
        </a>
    </div>

    <div class="admin-inline-note mb-6 px-5 py-4 text-sm leading-7">
        Uma igreja pode existir sem administrador local. Ela só fica operacional para missas, repertórios e publicações quando houver administrador local ativo vinculado.
    </div>

    @if (session('success'))
        <div class="mb-6 rounded border-l-4 border-green-500 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <article class="admin-section-card p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Total</p>
            <p class="mt-3 text-3xl font-black text-gray-900">{{ $totalIgrejas }}</p>
            <p class="mt-2 text-sm text-gray-500">Comunidades cadastradas no sistema.</p>
        </article>

        <article class="admin-section-card p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Operacionais</p>
            <p class="mt-3 text-3xl font-black text-blue-700">{{ $igrejasOperacionais }}</p>
            <p class="mt-2 text-sm text-gray-500">Igrejas já liberadas para rotina local.</p>
        </article>

        <article class="admin-section-card p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Aguardando vínculo</p>
            <p class="mt-3 text-3xl font-black text-amber-700">{{ $igrejasAguardando }}</p>
            <p class="mt-2 text-sm text-gray-500">Cadastros válidos que ainda dependem de administrador local.</p>
        </article>
    </div>

    <div class="space-y-5">
        @forelse ($igrejas as $igreja)
            @php($adminsLocais = $igreja->adminsLocais)
            @php($coordenadores = $igreja->coordenadores)
            @php($adminLocal = $adminsLocais->first())

            <article class="admin-section-card overflow-hidden p-5 sm:p-6">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                                <img src="{{ $igreja->imagemUrl() }}" alt="Imagem da igreja {{ $igreja->nome }}" class="h-full w-full object-cover">
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-black text-gray-900">{{ $igreja->nome }}</h2>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $igreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $igreja->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $igreja->estaOperacional() ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $igreja->statusOperacionalLabel() }}
                                    </span>
                                </div>

                                <p class="mt-2 break-all text-sm text-gray-500">{{ $igreja->slug }}</p>
                                <p class="mt-1 text-xs text-gray-400">CNPJ: {{ $igreja->cnpj }}</p>
                                <p class="mt-3 text-sm text-gray-600">
                                    {{ $igreja->cidade }} - {{ $igreja->estado }}
                                    @if ($igreja->endereco)
                                        <span class="text-gray-400">• {{ $igreja->endereco }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 xl:justify-end">
                            <a href="{{ route('admin.igrejas.edit', $igreja) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                Editar igreja
                            </a>
                            <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-[#6c4a21]/20 bg-[#f8f1e7] px-4 py-3 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#efe2cf]">
                                Página dos fiéis
                            </a>
                            @if ($adminLocal)
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100"
                                    data-reset-admin-local
                                    data-modal-id="resetar-admin-local-{{ $igreja->id }}-{{ $adminLocal->id }}"
                                >
                                    Resetar senha do admin local
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Status operacional</p>
                                <p class="mt-3 text-base font-bold text-gray-900">{{ $igreja->statusOperacionalLabel() }}</p>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ $igreja->estaOperacional() ? 'Liberada para operação local.' : 'Cadastro válido, aguardando administrador local ativo.' }}
                                </p>
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Administrador local</p>
                                @if ($adminLocal)
                                    <div class="mt-3 space-y-3">
                                        @foreach ($adminsLocais as $admin)
                                            <div class="@if (!$loop->first) border-t border-gray-200 pt-3 @endif">
                                                <p class="font-semibold text-gray-900">{{ $admin->nome }}</p>
                                                <p class="break-all text-sm text-gray-600">{{ $admin->email }}</p>
                                                @if ($admin->telefone)
                                                    <p class="text-sm text-gray-500">{{ $admin->telefone }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-3 text-sm font-medium text-amber-700">Nenhum administrador local vinculado.</p>
                                @endif
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-4 lg:col-span-2">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Coordenadores vinculados</p>
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                        {{ $coordenadores->count() }} {{ \Illuminate\Support\Str::plural('coordenador', $coordenadores->count()) }}
                                    </span>
                                </div>

                                @if ($coordenadores->isNotEmpty())
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach ($coordenadores as $coordenador)
                                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-3">
                                                <p class="font-semibold text-gray-900">{{ $coordenador->nome }}</p>
                                                <p class="break-all text-sm text-gray-600">{{ $coordenador->email }}</p>
                                                <p class="text-xs text-gray-400">{{ $coordenador->cpf }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-gray-500">Nenhum coordenador vinculado a esta igreja.</p>
                                @endif
                            </div>
                        </section>

                        <section class="rounded-3xl border border-gray-200 bg-gray-50 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Links públicos</p>

                            <div class="mt-4 space-y-4">
                                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Página dos fiéis</p>
                                    <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-semibold text-[#6c4a21] hover:underline">
                                        {{ $igreja->link_publico }}
                                    </a>
                                </div>

                                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Página dos músicos</p>
                                    <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-semibold text-slate-800 hover:underline">
                                        {{ $igreja->link_publico_musicos }}
                                    </a>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                                        Abrir QR dos fiéis
                                    </a>
                                    <a href="{{ $igreja->qr_code_url_musicos }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                                        Abrir QR dos músicos
                                    </a>
                                </div>

                                <p class="text-xs leading-6 text-gray-500">Cada igreja tem um link público para fiéis e outro específico para músicos. Os dois podem ser compartilhados separadamente conforme a publicação da missa.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-section-card p-10 text-center text-gray-500">
                Nenhuma igreja cadastrada até o momento.
            </div>
        @endforelse
    </div>

    @foreach ($igrejas as $igreja)
        @foreach ($igreja->adminsLocais as $adminLocal)
            <div id="resetar-admin-local-{{ $igreja->id }}-{{ $adminLocal->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4 py-6" data-reset-modal>
                <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Resetar senha do administrador local</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $adminLocal->nome }} • {{ $igreja->nome }}
                            </p>
                        </div>
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-gray-200 text-gray-500 hover:bg-gray-50" data-fechar-reset-modal>
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.igrejas.admin-local.password.reset', $igreja) }}" method="POST" class="mt-6 space-y-4" onsubmit="return confirm('Confirma a redefinição da senha deste administrador local?');">
                        @csrf
                        <input type="hidden" name="origem" value="index">
                        <input type="hidden" name="admin_local_id" value="{{ $adminLocal->id }}">

                        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                            Se você deixar a nova senha em branco, o sistema vai usar o CPF do administrador local como senha padrão e obrigar a troca no próximo acesso.
                        </div>

                        <div data-password-strength-container>
                            <label class="block text-sm font-medium text-gray-700">Nova senha manual</label>
                            <input type="password" name="password" data-password-strength-input class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-[#6c4a21] focus:ring-2 focus:ring-[#d6ad6c]/30" placeholder="Opcional">
                            @include('partials.password-strength-meter')
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                            <input type="password" name="password_confirmation" data-password-confirmation-input class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-[#6c4a21] focus:ring-2 focus:ring-[#d6ad6c]/30" placeholder="Repita a nova senha">
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-medium text-gray-700 hover:bg-gray-50" data-fechar-reset-modal>
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">
                                Confirmar redefinição
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const abrirBotoes = document.querySelectorAll('[data-reset-admin-local]');
            const modalInicialId = @json(session('abrir_reset_modal'));

            abrirBotoes.forEach((botao) => {
                const modalId = botao.getAttribute('data-modal-id');
                const modal = modalId ? document.getElementById(modalId) : null;

                if (!modal) {
                    return;
                }

                const fechar = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                };

                botao.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                });

                modal.querySelectorAll('[data-fechar-reset-modal]').forEach((controle) => {
                    controle.addEventListener('click', fechar);
                });

                modal.addEventListener('click', (evento) => {
                    if (evento.target === modal) {
                        fechar();
                    }
                });

                if (modalInicialId && modal.id === modalInicialId) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }
            });
        });
    </script>
    @include('partials.password-strength-script')
@endpush
