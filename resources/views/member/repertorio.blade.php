@extends('member.layouts.app')

@section('title', 'Meu repertorio | Voz & Cifra')
@section('mobile_title', 'Meu repertorio')
@section('desktop_subtitle', 'Repertorio da sua igreja para leitura e apoio')

@section('header_actions')
    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Biblioteca musical
    </a>
@endsection

@section('content')
    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Meu repertorio</h1>
                <p class="mt-2 text-sm text-gray-500">Acompanhe a missa ativa ou a proxima celebracao preparada para {{ $igreja?->nome ?: 'sua igreja' }}.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Painel</a>
                <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Biblioteca musical</a>
            </div>
        </div>
    </section>

    @if (!$missa)
        <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Ainda nao existe missa com repertorio disponivel</h2>
            <p class="mt-2 text-sm text-gray-500">Assim que a igreja montar a celebracao, as musicas vao aparecer aqui para estudo e leitura.</p>
        </div>
    @else
        <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $missa->titulo }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ optional($missa->data_missa)->format('d/m/Y') }} as {{ substr((string) $missa->hora_inicio, 0, 5) }} @if($missa->tempoLiturgico) • {{ $missa->tempoLiturgico->nome }} @endif</p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $missa->ativo ? 'Missa ativa' : 'Proxima missa' }}</span>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($missa->missaMusicas as $item)
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ordem {{ $item->ordem }}</span>
                                    @if ($item->momentoLiturgico)
                                        <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $item->momentoLiturgico->nome }}</span>
                                    @endif
                                </div>
                                <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $item->musica->titulo }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista nao informado' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-gray-700">Versao: {{ $item->versaoMusical?->titulo ?: 'Nao vinculada' }}</span>
                                    @if ($item->tom_exibicao)
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-amber-700">Tom da missa {{ $item->tom_exibicao }}</span>
                                    @endif
                                    @if ($item->tom_usado && $item->versaoMusical?->tom_musical)
                                        <span class="inline-flex rounded-full bg-gray-200 px-3 py-1 text-gray-700">Original {{ $item->versaoMusical->tom_musical }}</span>
                                    @endif
                                    @if ($item->versaoMusical?->bpm)
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">BPM {{ $item->versaoMusical->bpm }}</span>
                                    @endif
                                </div>
                            </div>

                            @if ($item->versaoMusical)
                                <a href="{{ route('member.versoes.show', [$item->musica, $item->versaoMusical]) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Abrir cifra</a>
                            @else
                                <span class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-500">Sem cifra vinculada</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">O repertorio desta missa ainda nao possui musicas cadastradas.</div>
                @endforelse
            </div>
        </section>
    @endif
@endsection

