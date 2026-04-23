@extends('admin.layouts.admin')

@section('title', 'Tempos liturgicos | Voz & Cifra')
@section('mobile_title', 'Tempos')

@section('content')
    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Catalogo liturgico</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Tempos liturgicos</h1>
                <p class="admin-page-copy mt-3 text-sm sm:text-base">Gerencie os tempos liturgicos centrais do sistema.</p>
            </div>

            <div class="admin-page-actions">
                <a href="{{ route('admin.tempos-liturgicos.create') }}" class="admin-btn admin-btn-primary">Novo tempo</a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="admin-table-shell">
            @if ($temposLiturgicos->isEmpty())
                <div class="admin-empty-state">
                    Nenhum tempo liturgico cadastrado ate o momento.
                </div>
            @else
                <div class="admin-table-wrap hidden lg:block">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Descricao</th>
                                <th>Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($temposLiturgicos as $tempoLiturgico)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-gray-800">{{ $tempoLiturgico->nome }}</div>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $tempoLiturgico->descricao ?: 'Sem descricao cadastrada.' }}
                                    </td>
                                    <td>
                                        <span class="admin-badge {{ $tempoLiturgico->ativo ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                            {{ $tempoLiturgico->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-actions justify-end">
                                            <a href="{{ route('admin.tempos-liturgicos.edit', $tempoLiturgico) }}" class="admin-btn admin-btn-secondary">Editar</a>
                                            <form action="{{ route('admin.tempos-liturgicos.destroy', $tempoLiturgico) }}" method="POST" onsubmit="return confirm('Deseja excluir este tempo liturgico?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn admin-btn-danger">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="space-y-4 p-4 lg:hidden">
                    @foreach ($temposLiturgicos as $tempoLiturgico)
                        <article class="admin-list-card p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold text-gray-800">{{ $tempoLiturgico->nome }}</h2>
                                    <p class="mt-2 text-sm text-gray-600">{{ $tempoLiturgico->descricao ?: 'Sem descricao cadastrada.' }}</p>
                                </div>
                                <span class="admin-badge shrink-0 {{ $tempoLiturgico->ativo ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                    {{ $tempoLiturgico->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <div class="admin-actions mt-4">
                                <a href="{{ route('admin.tempos-liturgicos.edit', $tempoLiturgico) }}" class="admin-btn admin-btn-secondary">Editar</a>
                                <form action="{{ route('admin.tempos-liturgicos.destroy', $tempoLiturgico) }}" method="POST" onsubmit="return confirm('Deseja excluir este tempo liturgico?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-danger">Excluir</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
