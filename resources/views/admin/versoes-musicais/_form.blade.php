@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $letraInicial = old('letra_com_cifras', $versaoMusical->letra_com_cifras ?? $musica->letra ?? '');
@endphp

@push('styles')
    @vite('resources/css/admin/versoes-musicais-form.css')
@endpush

@if (session('info'))
    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 text-sm rounded">
        {{ session('info') }}
    </div>
@endif

@if (session('warning'))
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 text-sm rounded">
        {{ session('warning') }}
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.95fr)] gap-6">
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-5">
                <div data-guide-target="cifra-editor">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Letra com cifras</label>
                        <span class="text-xs text-gray-500">Edite a cifra e acompanhe a prévia.</span>
                    </div>
                    <div class="mt-3 cifra-toolbar">
                        <div class="cifra-toolbar-main">
                            <button type="button" class="cifra-action-primary" data-organizar-cifra-visual data-guide-target="cifra-organizar">
                                Arrumar cifra
                            </button>
                            <button type="button" class="cifra-action-secondary" data-cifra-club-mode>
                                Colar formato Cifra Club
                            </button>
                        </div>

                        <details class="cifra-more-tools" data-guide-target="cifra-ferramentas">
                            <summary>Ajustes avançados</summary>
                            <div class="cifra-more-tools-panel">
                                <button type="button" class="cifra-mini-tool" data-inserir-marcacao="Refrão:\n">
                                    Inserir seção: refrão
                                </button>
                                <button type="button" class="cifra-mini-tool" data-marcar-linha="Refrão:">
                                    Transformar linha em refrão
                                </button>
                                <button type="button" class="cifra-mini-tool" data-inserir-marcacao="[Primeira parte]\n">
                                    Inserir seção: parte
                                </button>
                                <button type="button" class="cifra-mini-tool" data-marcar-linha="[Primeira parte]">
                                    Transformar linha em parte
                                </button>
                                <button type="button" class="cifra-mini-tool" data-inserir-marcacao="[D] [C]\n">
                                    Inserir linha de acordes
                                </button>
                            </div>
                        </details>
                    </div>
                    <textarea id="letra_com_cifras" name="letra_com_cifras" rows="18" required spellcheck="false" autocomplete="off" autocapitalize="off" placeholder="[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus" class="{{ $classeInput }} font-mono text-sm">{{ $letraInicial }}</textarea>

                    <div class="mt-3">
                        <pre id="preview_padrao_interno" class="hidden"></pre>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4" data-guide-target="cifra-musica-base">
                    <label class="block text-sm font-medium text-gray-700">Musica base</label>
                    <input type="text" value="{{ $musica->titulo }}" disabled class="{{ $classeInput }} bg-gray-50 text-gray-500 cursor-not-allowed" />
                </div>

                <div data-guide-target="cifra-titulo">
                    <label class="block text-sm font-medium text-gray-700">Titulo da versao</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $versaoMusical->titulo ?? '') }}" placeholder="Ex.: Tom original, Versao para assembleia" class="{{ $classeInput }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" data-guide-target="cifra-tom-bpm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tom musical</label>
                        <select name="tom_musical" class="{{ $classeInput }}">
                            <option value="">Selecione um tom</option>
                            @foreach (($tonsMusicais ?? []) as $tomMusical)
                                <option value="{{ $tomMusical }}" @selected(old('tom_musical', $versaoMusical->tom_musical ?? '') === $tomMusical)>
                                    {{ $tomMusical }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">BPM</label>
                        <input type="number" name="bpm" min="1" max="999" value="{{ old('bpm', $versaoMusical->bpm ?? '') }}" placeholder="Ex.: 72" class="{{ $classeInput }}" />
                    </div>
                </div>

                <div data-guide-target="cifra-youtube">
                    <label class="block text-sm font-medium text-gray-700">YouTube video ID</label>
                    <input type="text" name="youtube_video_id" value="{{ old('youtube_video_id', $versaoMusical->youtube_video_id ?? '') }}" placeholder="Ex.: dQw4w9WgXcQ ou cole a URL" class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $versaoMusical->ativo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Versao ativa</label>
                </div>
            </div>
        </div>

    </div>

    <div class="space-y-6">
        <div class="preview-cifra-sticky bg-white p-6 rounded-2xl shadow-sm border border-gray-100" data-guide-target="cifra-preview">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Pr&eacute;via da cifra</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <button type="button" class="rounded-full bg-green-700 px-4 py-2 text-sm font-semibold text-white ring-2 ring-green-200" data-preview-toggle="com-cifras" aria-pressed="true">
                    Prévia músico
                </button>
                <button type="button" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700" data-preview-toggle="sem-cifras" aria-pressed="false">
                    Sem cifra
                </button>
            </div>

            <div class="space-y-4">
                <div data-preview-panel="com-cifras">
                    <div id="preview_com_cifras" class="editor-cifra-preview min-h-[520px] max-h-[72vh] rounded-xl border border-[#ead6b3] bg-white p-5 text-gray-900 overflow-auto" style="background:#ffffff;color:#172033;"></div>
                </div>

                <div class="hidden" data-preview-panel="sem-cifras">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visão sem cifra</h3>
                    <div id="preview_sem_cifras" class="min-h-[520px] max-h-[72vh] rounded-xl bg-gray-50 p-5 text-gray-800 border border-gray-200 overflow-auto"></div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/versoes-musicais-form.js')
@endpush


