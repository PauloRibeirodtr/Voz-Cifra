@extends('admin.layouts.admin')

@section('title', 'Igrejas | Voz & Cifra')
@section('mobile_title', 'Igrejas')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Igrejas cadastradas</h1>
            <p class="text-sm text-gray-500">Gerencie as igrejas, seus coordenadores, admins locais e os dois links publicos de cada comunidade.</p>
        </div>

        <a href="{{ route('admin.igrejas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800 sm:w-auto">
            Nova igreja
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            {{ session('error') }}
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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($igrejas->isEmpty())
            <div class="p-8 text-center text-gray-500">
                Nenhuma igreja cadastrada ate o momento.
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Igreja</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Localizacao</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Admins locais</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Link publico</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($igrejas as $igreja)
                            @php($adminsLocais = $igreja->adminsLocais)
                            @php($coordenadores = $igreja->coordenadores)
                            @php($adminLocal = $adminsLocais->first())
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $igreja->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $igreja->slug }}</div>
                                    <div class="text-xs text-gray-400">CNPJ: {{ $igreja->cnpj }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>{{ $igreja->cidade }} - {{ $igreja->estado }}</div>
                                    @if ($igreja->endereco)
                                        <div class="text-xs text-gray-400">{{ $igreja->endereco }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @if ($coordenadores->isNotEmpty())
                                        <div class="mb-3 rounded-xl border border-amber-100 bg-amber-50 p-3">
                                            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Coordenadores</div>
                                            @foreach ($coordenadores as $coordenador)
                                                <div class="@if(!$loop->first) mt-3 border-t border-amber-100 pt-3 @endif">
                                                    <div class="font-medium text-gray-800">{{ $coordenador->nome }}</div>
                                                    <div>{{ $coordenador->email }}</div>
                                                    <div class="text-xs text-gray-400">{{ $coordenador->cpf }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($adminLocal)
                                        @foreach ($adminsLocais as $admin)
                                            <div class="@if(!$loop->first) mt-3 border-t border-gray-100 pt-3 @endif">
                                                <div class="font-medium text-gray-800">{{ $admin->nome }}</div>
                                                <div>{{ $admin->email }}</div>
                                                @if ($admin->telefone)
                                                    <div>{{ $admin->telefone }}</div>
                                                @endif
                                                <div class="text-xs text-gray-400">{{ $admin->cpf }}</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-amber-600 font-medium">Sem admin local</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $igreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $igreja->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="break-all text-green-700 hover:underline">
                                        {{ $igreja->link_publico }}
                                    </a>
                                    <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-slate-900 hover:underline">
                                        {{ $igreja->link_publico_musicos }}
                                    </a>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Primeiro link para fieis, segundo para musicos.
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            QR fieis
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.igrejas.edit', $igreja) }}" class="inline-flex px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Editar
                                        </a>
                                        @if ($adminLocal)
                                            <button
                                                type="button"
                                                class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100"
                                                data-reset-admin-local
                                                data-modal-id="resetar-admin-local-{{ $igreja->id }}-{{ $adminLocal->id }}"
                                            >
                                                Resetar senha
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 md:hidden">
                @foreach ($igrejas as $igreja)
                    @php($adminsLocais = $igreja->adminsLocais)
                    @php($coordenadores = $igreja->coordenadores)
                    @php($adminLocal = $adminsLocais->first())
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="break-words text-base font-bold text-gray-800">{{ $igreja->nome }}</h2>
                                <p class="mt-1 break-all text-sm text-gray-500">{{ $igreja->slug }}</p>
                                <p class="mt-1 text-xs text-gray-400">CNPJ: {{ $igreja->cnpj }}</p>
                            </div>

                            <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $igreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $igreja->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-gray-600">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Localizacao</span>
                                <div class="mt-1">{{ $igreja->cidade }} - {{ $igreja->estado }}</div>
                                @if ($igreja->endereco)
                                    <div class="mt-1 text-xs text-gray-400">{{ $igreja->endereco }}</div>
                                @endif
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Admins locais</span>
                                @if ($coordenadores->isNotEmpty())
                                    <div class="mt-2 rounded-xl border border-amber-100 bg-amber-50 p-3">
                                        <span class="block text-[11px] font-bold uppercase tracking-wider text-amber-700">Coordenadores</span>
                                        @foreach ($coordenadores as $coordenador)
                                            <div class="@if(!$loop->first) mt-3 border-t border-amber-100 pt-3 @endif">
                                                <div class="mt-1 font-semibold text-gray-800">{{ $coordenador->nome }}</div>
                                                <div class="break-all">{{ $coordenador->email }}</div>
                                                <div class="text-xs text-gray-400">{{ $coordenador->cpf }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($adminLocal)
                                    @foreach ($adminsLocais as $admin)
                                        <div class="@if(!$loop->first) mt-3 border-t border-gray-200 pt-3 @endif">
                                            <div class="mt-1 font-semibold text-gray-800">{{ $admin->nome }}</div>
                                            <div class="break-all">{{ $admin->email }}</div>
                                            @if ($admin->telefone)
                                                <div>{{ $admin->telefone }}</div>
                                            @endif
                                            <div class="text-xs text-gray-400">{{ $admin->cpf }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="mt-1 font-medium text-amber-600">Sem admin local</div>
                                @endif
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Acessos publicos</span>
                                <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm font-medium text-green-700 hover:underline">
                                    {{ $igreja->link_publico }}
                                </a>
                                <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-medium text-slate-900 hover:underline">
                                    {{ $igreja->link_publico_musicos }}
                                </a>
                                <p class="mt-2 text-xs text-gray-500">Primeiro link para fieis, segundo para musicos.</p>
                                <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Ver QR dos fieis
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2">
                            <a href="{{ route('admin.igrejas.edit', $igreja) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar igreja
                            </a>
                            @if ($adminLocal)
                                <button
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                                    data-reset-admin-local
                                    data-modal-id="resetar-admin-local-{{ $igreja->id }}-{{ $adminLocal->id }}"
                                >
                                    Resetar senha do admin local
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @foreach ($igrejas as $igreja)
                @foreach ($igreja->adminsLocais as $adminLocal)
                    <div id="resetar-admin-local-{{ $igreja->id }}-{{ $adminLocal->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4 py-6" data-reset-modal>
                        <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">Resetar senha do admin local</h2>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $adminLocal->nome }} • {{ $igreja->nome }}
                                    </p>
                                </div>
                                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-gray-200 text-gray-500 hover:bg-gray-50" data-fechar-reset-modal>
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <form action="{{ route('admin.igrejas.admin-local.password.reset', $igreja) }}" method="POST" class="mt-6 space-y-4" onsubmit="return confirm('Confirma a redefinicao da senha deste admin local?');">
                                @csrf
                                <input type="hidden" name="origem" value="index">
                                <input type="hidden" name="admin_local_id" value="{{ $adminLocal->id }}">

                                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                                    Se voce deixar a nova senha em branco, o sistema vai usar o CPF do admin local como senha padrao e obrigar a troca no proximo acesso.
                                </div>

                                <div data-password-strength-container>
                                    <label class="block text-sm font-medium text-gray-700">Nova senha manual</label>
                                    <input type="password" name="password" data-password-strength-input class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Opcional">
                                    @include('partials.password-strength-meter')
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                                    <input type="password" name="password_confirmation" data-password-confirmation-input class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Repita a nova senha">
                                </div>

                                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50" data-fechar-reset-modal>
                                        Cancelar
                                    </button>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">
                                        Confirmar reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>
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
