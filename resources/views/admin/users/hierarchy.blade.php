@extends('admin.layouts.admin')

@section('title', 'Hierarquia de Usuarios | Voz & Cifra')
@section('mobile_title', 'Hierarquia')
@section('desktop_subtitle', 'Visao simples das contas abaixo do admin master')

@section('content')
    @php
        $corStatus = static fn (bool $ativo): string => $ativo
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-red-100 text-red-700';

        $rotuloPerfil = static function (\App\Models\Usuario $usuario): string {
            return match ($usuario->perfil_global) {
                'admin_master' => 'Admin master',
                default => 'Usuario',
            };
        };
    @endphp

    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">Hierarquia de usuarios</h1>
        <p class="mt-2 max-w-3xl text-sm text-gray-500 sm:text-base">
            Esta tela mostra quem esta abaixo de voce na estrutura do sistema. A ideia e facilitar a administracao central sem exigir leitura tecnica.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6 sm:mb-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Seu nivel</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $nivelUsuarioAtual }}</div>
            <p class="mt-2 text-sm text-slate-500">Voce esta no topo da arvore operacional atual.</p>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Admins master abaixo</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $totais['admins_master_abaixo'] }}</div>
            <p class="mt-2 text-sm text-slate-500">Usuarios globais que podem ser acompanhados e gerenciados por voce.</p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">Admins locais</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $totais['admins_locais'] }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $totais['igrejas_com_admin_local'] }} igrejas com lideranca local visivel.</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-amber-600">Membros</span>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $totais['membros'] }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $totais['igrejas_com_membros'] }} igrejas com membros cadastrados.</p>
        </div>
    </div>

    <div class="mb-6 sm:mb-8 rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-sky-300">Voce esta aqui</p>
                <h2 class="mt-2 text-2xl font-black">{{ $usuarioAtual->nome }}</h2>
                <p class="mt-2 text-sm text-slate-200">{{ $rotuloPerfil($usuarioAtual) }}</p>
                <p class="mt-1 text-sm text-slate-300">{{ $usuarioAtual->email }}</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-300">Regra principal</div>
                    <p class="mt-2 text-sm text-white">Voce gerencia apenas usuarios com nivel inferior ao seu.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-300">Leitura visual</div>
                    <p class="mt-2 text-sm text-white">Primeiro aparecem os admins globais, depois os admins locais e por fim os membros.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-gray-800">Admins master abaixo de voce</h2>
                    <p class="mt-1 text-sm text-gray-500">Camada global imediatamente abaixo da conta principal.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ $adminsMasterAbaixo->count() }} usuarios
                </span>
            </div>

            @if ($adminsMasterAbaixo->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-sm text-gray-500">
                    Nenhum admin master inferior foi encontrado neste momento.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    @foreach ($adminsMasterAbaixo as $admin)
                        <article class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-bold text-gray-800">{{ $admin->nome }}</h3>
                                    <p class="mt-1 break-all text-sm text-gray-600">{{ $admin->email }}</p>
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">{{ $rotuloPerfil($admin) }}</p>
                                </div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corStatus((bool) $admin->ativo) }}">
                                    {{ $admin->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Primeiro acesso</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-700">{{ $admin->primeiro_acesso ? 'Sim' : 'Nao' }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Telefone</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-700">{{ $admin->telefone ?: 'Nao informado' }}</div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-gray-800">Admins locais por igreja</h2>
                    <p class="mt-1 text-sm text-gray-500">Uma visao simples de quem lidera cada igreja no nivel local.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $totais['admins_locais'] }} usuarios
                </span>
            </div>

            <div class="space-y-4">
                @forelse ($adminsLocaisPorIgreja as $igrejaNome => $usuarios)
                    <article class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-5">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-black text-gray-800">{{ $igrejaNome }}</h3>
                                <p class="mt-1 text-sm text-gray-500">Admins locais vinculados a esta igreja.</p>
                            </div>
                            <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'admin local' : 'admins locais' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            @foreach ($usuarios as $usuario)
                                <div class="rounded-2xl border border-emerald-100 bg-white p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate text-base font-bold text-gray-800">{{ $usuario->nome }}</div>
                                            <div class="mt-1 break-all text-sm text-gray-600">{{ $usuario->email }}</div>
                                            <div class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">{{ $rotuloPerfil($usuario) }}</div>
                                        </div>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corStatus((bool) $usuario->ativo) }}">
                                            {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-sm text-gray-500">
                        Nenhum admin local foi encontrado.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-gray-800">Membros por igreja</h2>
                    <p class="mt-1 text-sm text-gray-500">Base de usuarios finais organizada por igreja para facilitar localizacao.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ $totais['membros'] }} usuarios
                </span>
            </div>

            <div class="space-y-4">
                @forelse ($membrosPorIgreja as $igrejaNome => $usuarios)
                    <article class="rounded-2xl border border-amber-100 bg-amber-50/40 p-5">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-black text-gray-800">{{ $igrejaNome }}</h3>
                                <p class="mt-1 text-sm text-gray-500">Membros vinculados a esta igreja.</p>
                            </div>
                            <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'membro' : 'membros' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                            @foreach ($usuarios as $usuario)
                                <div class="rounded-2xl border border-amber-100 bg-white p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate text-base font-bold text-gray-800">{{ $usuario->nome }}</div>
                                            <div class="mt-1 break-all text-sm text-gray-600">{{ $usuario->email }}</div>
                                            <div class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">{{ $rotuloPerfil($usuario) }}</div>
                                        </div>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corStatus((bool) $usuario->ativo) }}">
                                            {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-sm text-gray-500">
                        Nenhum membro foi encontrado.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
