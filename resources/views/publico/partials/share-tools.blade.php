@props([
    'url',
    'title' => 'Voz & Cifra',
    'text' => 'Acompanhe esta celebração no Voz & Cifra.',
])

@php
    $shareUrl = (string) $url;
    $shareTitle = (string) $title;
    $shareText = (string) $text;
    $whatsAppText = trim($shareText . ' ' . $shareUrl);
@endphp

<details
    class="public-share-tools"
    data-public-share-tools
    data-share-url="{{ $shareUrl }}"
    data-share-title="{{ $shareTitle }}"
    data-share-text="{{ $shareText }}"
>
    <summary
        class="public-tool-button public-share-trigger"
        data-public-share-trigger
        aria-expanded="false"
        aria-label="Abrir opções de compartilhamento"
    >
        <span class="public-share-trigger__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false" role="img">
                <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7a3.3 3.3 0 0 0 0-1.39l7.05-4.1A2.99 2.99 0 1 0 15 5c0 .23.03.45.08.66l-7.05 4.1a3 3 0 1 0 0 4.48l7.12 4.16c-.04.18-.06.37-.06.56a3 3 0 1 0 3-2.88z"></path>
            </svg>
        </span>
        <span class="public-share-trigger__label">Compartilhar</span>
    </summary>
    <div class="public-share-menu" data-public-share-menu>
        <button type="button" class="public-share-menu__item" data-public-copy-link>
            Copiar link
        </button>
        <button type="button" class="public-share-menu__item" data-public-native-share>
            Compartilhar
        </button>
        <a
            href="https://wa.me/?text={{ rawurlencode($whatsAppText) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="public-share-menu__item public-share-menu__item--whatsapp"
        >
            WhatsApp
        </a>
    </div>
    <span class="public-share-feedback" data-public-share-feedback hidden>Link copiado.</span>
</details>
