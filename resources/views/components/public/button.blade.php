@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = [
        'primary' => 'background: linear-gradient(135deg, #d2aa66, #b8884e); color: #1a130e; border: 1px solid rgba(241, 213, 167, 0.35);',
        'secondary' => 'background: rgba(26, 19, 14, 0.55); color: #f5efe6; border: 1px solid rgba(210, 170, 102, 0.18);',
        'ghost' => 'background: transparent; color: #d2aa66; border: 1px solid rgba(210, 170, 102, 0.18);',
    ];

    $style = 'display:inline-flex;align-items:center;justify-content:center;min-height:3.2rem;padding:0.95rem 1.35rem;border-radius:999px;font-weight:800;letter-spacing:0.01em;transition:transform .22s ease,box-shadow .22s ease;background:#d2aa66;border:1px solid rgba(241,213,167,.35);box-shadow:0 16px 30px rgba(0,0,0,.22);'
        . ($classes[$variant] ?? $classes['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" style="{{ $style }}" {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" style="{{ $style }}" {{ $attributes }}>
        {{ $slot }}
    </button>
@endif
