@extends('member.layouts.app')

@section('title', 'Meu repertório | Voz & Cifra')
@section('mobile_title', 'Meu repertório')
@section('desktop_subtitle', 'Repertório da sua igreja para leitura e apoio')

@section('header_actions')
    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Biblioteca musical
    </a>
@endsection

@push('styles')
    @include('partials.cifra-viewer-styles')
    <style>
        .repertorio-card {
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
        }

        .repertorio-cifra-box {
            border-radius: 1.5rem;
            border: 1px solid rgba(140, 105, 51, 0.14);
            background: linear-gradient(180deg, #fffdfa 0%, #f7efe3 100%);
            color: #1f2937;
            overflow: hidden;
        }

        .repertorio-cifra-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1rem 0;
        }

        .repertorio-cifra-preview {
            max-height: 58vh;
            overflow-y: auto;
            padding: 1rem 1rem 1.15rem;
        }

        .repertorio-cifra-preview .cifra-acordes {
            color: #c56a1a;
            font-size: 0.98rem;
        }

        .repertorio-cifra-preview .cifra-acorde {
            background: rgba(197, 106, 26, 0.1);
            padding: 0.08rem 0.35rem;
        }

        .repertorio-cifra-preview .cifra-letra {
            color: #1f2937;
            line-height: 1.9rem;
        }

        .repertorio-acorde-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            border-radius: 9999px;
            border: 1px solid rgba(197, 106, 26, 0.18);
            background: rgba(197, 106, 26, 0.08);
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 800;
            color: #8a4b12;
        }

        @media (max-width: 767px) {
            .repertorio-cifra-preview {
                max-height: none;
            }
        }
    </style>
@endpush

@section('content')
    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Meu repertório</h1>
                <p class="mt-2 text-sm text-gray-500">Acompanhe a missa ativa ou a próxima celebração preparada para {{ $igreja?->nome ?: 'sua igreja' }}.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Painel</a>
                <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Biblioteca musical</a>
            </div>
        </div>
    </section>

    @if (!$missa)
        <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Ainda não existe missa com repertório disponível</h2>
            <p class="mt-2 text-sm text-gray-500">Assim que a igreja montar a celebração, as músicas vão aparecer aqui para estudo e leitura.</p>
        </div>
    @else
        <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $missa->titulo }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ optional($missa->data_missa)->format('d/m/Y') }} às {{ substr((string) $missa->hora_inicio, 0, 5) }} @if($missa->tempoLiturgico) &bull; {{ $missa->tempoLiturgico->nome }} @endif</p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $missa->ativo ? 'Missa ativa' : 'Próxima missa' }}</span>
            </div>

            <div class="mt-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                Esta tela prioriza leitura rápida. Sempre que houver versão musical vinculada, a cifra aparece diretamente aqui para reduzir navegação durante ensaio ou missa.
            </div>

            <div class="mt-6 space-y-5">
                @forelse ($missa->missaMusicas as $item)
                    @php
                        $textoCifra = (string) ($item->versaoMusical?->letra_com_cifras ?? '');
                    @endphp

                    <article class="repertorio-card p-5 sm:p-6">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ordem {{ $item->ordem }}</span>
                                        @if ($item->momentoLiturgico)
                                            <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $item->momentoLiturgico->nome }}</span>
                                        @endif
                                        @if ($item->versaoMusical)
                                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-700">Versão: {{ $item->versaoMusical->titulo ?: 'Principal' }}</span>
                                        @endif
                                    </div>

                                    <h3 class="mt-3 text-xl font-black text-gray-900">{{ $item->musica->titulo }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista não informado' }}</p>

                                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                        @if ($item->tom_exibicao)
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-amber-700">Tom da missa {{ $item->tom_exibicao }}</span>
                                        @endif
                                        @if ($item->tom_usado && $item->versaoMusical?->tom_musical)
                                            <span class="inline-flex rounded-full bg-gray-200 px-3 py-1 text-gray-700">Tom original {{ $item->versaoMusical->tom_musical }}</span>
                                        @endif
                                        @if ($item->versaoMusical?->bpm)
                                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">BPM {{ $item->versaoMusical->bpm }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    @if ($item->versaoMusical)
                                        <a href="{{ route('member.versoes.show', [$item->musica, $item->versaoMusical]) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">
                                            Modo de estudo
                                        </a>
                                        <a href="{{ route('member.versoes.pdf', [$item->musica, $item->versaoMusical]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            PDF
                                        </a>
                                    @else
                                        <span class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-500">Sem cifra vinculada</span>
                                    @endif
                                </div>
                            </div>

                            @if ($item->versaoMusical)
                                <div class="repertorio-cifra-box">
                                    <div class="repertorio-cifra-toolbar">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#8c6933]">Leitura rápida</p>
                                            <p class="mt-1 text-sm text-gray-600">Cifra pronta para consulta imediata durante a celebração.</p>
                                        </div>

                                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                            <span class="inline-flex rounded-full border border-[#8c6933]/15 bg-white px-3 py-1 text-gray-700">Visualização da missa</span>
                                            @if ($item->momentoLiturgico)
                                                <span class="inline-flex rounded-full border border-[#8c6933]/15 bg-white px-3 py-1 text-gray-700">{{ $item->momentoLiturgico->nome }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="repertorio-cifra-preview">
                                        <div class="mb-4 flex flex-wrap gap-2" data-repertorio-acordes></div>
                                        <div
                                            data-repertorio-cifra
                                            data-texto-cifra='@json($textoCifra, JSON_UNESCAPED_UNICODE)'
                                            class="space-y-2"
                                        ></div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-5 text-sm text-gray-500">
                                    Esta música ainda não possui uma versão com cifra pronta para leitura rápida.
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">O repertório desta missa ainda não possui músicas cadastradas.</div>
                @endforelse
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    @include('partials.chord-transposer-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const helper = window.VozECifraChord;

            if (!helper) {
                return;
            }

            document.querySelectorAll('[data-repertorio-cifra]').forEach((container) => {
                const texto = container.dataset.textoCifra || '';
                const wrapper = container.closest('.repertorio-cifra-box');
                const listaAcordes = wrapper?.querySelector('[data-repertorio-acordes]');

                container.innerHTML = helper.renderChordSheetHtml(texto, {
                    chordAttribute: 'data-acorde-hover',
                });

                const acordes = helper.extractChordsFromBracketedText(texto);

                if (listaAcordes && acordes.length > 0) {
                    listaAcordes.innerHTML = acordes
                        .map((acorde) => `<span class="repertorio-acorde-chip">${helper.escapeHtml(acorde)}</span>`)
                        .join('');
                } else if (listaAcordes) {
                    listaAcordes.innerHTML = '<span class="text-xs text-slate-400">Nenhum acorde identificado nesta cifra.</span>';
                }
            });
        });
    </script>
@endpush
