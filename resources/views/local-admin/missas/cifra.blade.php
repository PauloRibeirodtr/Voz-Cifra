@extends('local-admin.layouts.admin')

@section('title', 'Visualizacao com cifra | Voz & Cifra')
@section('mobile_title', 'Cifra')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $itemRepertorio->musica->titulo }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Missa: {{ $missa->titulo }}
                @if ($itemRepertorio->momentoLiturgico)
                    • {{ $itemRepertorio->momentoLiturgico->nome }}
                @endif
            </p>
        </div>

        <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
            Voltar para a missa
        </a>
    </div>

    @if ($itemRepertorio->versaoMusical)
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white">Leitura da igreja</span>
                            <span id="tom_atual_badge" class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                Tom {{ $itemRepertorio->versaoMusical->tom_musical ?: 'Nao informado' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Tom</span>
                                <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">-</button>
                                <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Original</button>
                                <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">+</button>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Fonte</span>
                                <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">A-</button>
                                <button type="button" data-font-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Padrao</button>
                                <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">A+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        {{ $itemRepertorio->versaoMusical->titulo ?: 'Versao principal' }}
                    </span>
                    @if ($itemRepertorio->versaoMusical->tom_musical)
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tom {{ $itemRepertorio->versaoMusical->tom_musical }}</span>
                    @endif
                    @if ($itemRepertorio->versaoMusical->bpm)
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">BPM {{ $itemRepertorio->versaoMusical->bpm }}</span>
                    @endif
                </div>

                <div class="rounded-2xl bg-gray-900 p-5 text-green-200 shadow-inner">
                    <pre id="letra_com_cifras_preview" class="whitespace-pre-wrap break-words text-sm leading-7">{{ $itemRepertorio->versaoMusical->letra_com_cifras }}</pre>
                </div>
            </section>

            <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Resumo da versao</h2>
                <div class="mt-4 space-y-4 text-sm text-gray-600">
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Musica</span><span>{{ $itemRepertorio->musica->titulo }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Versao</span><span>{{ $itemRepertorio->versaoMusical->titulo ?: 'Versao principal' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tom</span><span>{{ $itemRepertorio->versaoMusical->tom_musical ?: 'Nao informado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">BPM</span><span>{{ $itemRepertorio->versaoMusical->bpm ?: 'Nao informado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Momento liturgico</span><span>{{ $itemRepertorio->momentoLiturgico?->nome ?: 'Nao definido' }}</span></div>
                </div>
            </aside>
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-sm">
            <h2 class="text-lg font-bold">Nenhuma versao musical vinculada</h2>
            <p class="mt-2 text-sm leading-7">
                Este item do repertorio ainda nao possui uma versao musical com cifra vinculada. Por enquanto, a missa pode usar apenas a musica base.
            </p>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($itemRepertorio->versaoMusical)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const preview = document.getElementById('letra_com_cifras_preview');
                const tomBadge = document.getElementById('tom_atual_badge');
                const textoOriginal = @json($itemRepertorio->versaoMusical->letra_com_cifras, JSON_UNESCAPED_UNICODE);
                const tomOriginal = @json($itemRepertorio->versaoMusical->tom_musical);
                let transposicaoAtual = 0;
                let fonteAtual = 14;

                if (!preview) {
                    return;
                }

                const escalaSustenidos = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
                const escalaBemol = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

                const ehAcorde = (valor) => /^[A-G](?:#|b)?(?:[a-zA-Z0-9º°+\-]*(?:\([^)\]]+\))?)?(?:\/[A-G](?:#|b)?)?$/.test((valor || '').trim());

                const transporNota = (nota, passos) => {
                    const usaBemol = nota.includes('b');
                    const escala = usaBemol ? escalaBemol : escalaSustenidos;
                    const indice = escala.indexOf(nota);

                    if (indice === -1) {
                        return nota;
                    }

                    const novoIndice = (indice + passos + 120) % 12;
                    return escala[novoIndice];
                };

                const transporAcorde = (acorde, passos) => {
                    const match = acorde.match(/^([A-G](?:#|b)?)(.*?)(?:\/([A-G](?:#|b)?))?$/);

                    if (!match) {
                        return acorde;
                    }

                    const [, tonica, sufixo, baixo] = match;
                    const novaTonica = transporNota(tonica, passos);
                    const novoBaixo = baixo ? '/' + transporNota(baixo, passos) : '';

                    return novaTonica + sufixo + novoBaixo;
                };

                const transporTexto = (texto, passos) => texto.replace(/\[([^\[\]\r\n]+)\]/g, (match, possivelAcorde) => {
                    return ehAcorde(possivelAcorde) ? '[' + transporAcorde(possivelAcorde, passos) + ']' : match;
                });

                const atualizarTomBadge = () => {
                    if (!tomBadge) {
                        return;
                    }

                    if (!tomOriginal || !ehAcorde(tomOriginal)) {
                        tomBadge.textContent = 'Tom nao informado';
                        return;
                    }

                    const tomAtual = transporAcorde(tomOriginal, transposicaoAtual);
                    tomBadge.textContent = 'Tom ' + tomAtual;
                };

                const renderizar = () => {
                    preview.textContent = transporTexto(textoOriginal, transposicaoAtual);
                    preview.style.fontSize = fonteAtual + 'px';
                    preview.style.lineHeight = Math.max(1.8, fonteAtual / 8) + '';
                    atualizarTomBadge();
                };

                document.querySelectorAll('[data-transpose]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        transposicaoAtual += Number(botao.dataset.transpose || 0);
                        renderizar();
                    });
                });

                document.querySelector('[data-transpose-reset]')?.addEventListener('click', () => {
                    transposicaoAtual = 0;
                    renderizar();
                });

                document.querySelectorAll('[data-font]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        fonteAtual = Math.min(24, Math.max(12, fonteAtual + (Number(botao.dataset.font || 0) * 2)));
                        renderizar();
                    });
                });

                document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
                    fonteAtual = 14;
                    renderizar();
                });

                renderizar();
            });
        </script>
    @endif
@endpush
