@props([
    'status' => ['label' => 'Publicada', 'slug' => 'publicada'],
])

@php
    $styles = [
        'acontecendo_agora' => 'background:rgba(79,143,103,.18);color:#d4ffe0;border-color:rgba(79,143,103,.34);',
        'preparada' => 'background:rgba(210,170,102,.16);color:#f4dfbd;border-color:rgba(210,170,102,.28);',
        'rascunho' => 'background:rgba(139,74,63,.16);color:#f3c4ba;border-color:rgba(139,74,63,.28);',
        'encerrada' => 'background:rgba(122,104,87,.18);color:#e0d0c0;border-color:rgba(168,145,122,.26);',
        'historico' => 'background:rgba(80,68,57,.22);color:#d7c7b6;border-color:rgba(156,130,106,.24);',
        'publicada' => 'background:rgba(102,84,63,.22);color:#f4dfbd;border-color:rgba(210,170,102,.22);',
    ];
@endphp

<span
    {{ $attributes->merge(['style' => 'display:inline-flex;align-items:center;width:fit-content;border-radius:999px;padding:.52rem .82rem;border:1px solid rgba(210,170,102,.18);font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;'
        . ($styles[$status['slug'] ?? 'publicada'] ?? $styles['publicada'])]) }}
>
    {{ $status['label'] ?? 'Publicada' }}
</span>
